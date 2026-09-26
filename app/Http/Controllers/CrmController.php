<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Deal;
use App\Models\User;
use App\Support\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CrmController extends Controller
{
    public function dashboard()
    {
        $id = Workspace::id();
        $contacts = Contact::forCompany($id)->count();
        $openDeals = Deal::forCompany($id)->whereNotIn('stage', ['won', 'lost'])->count();
        $pipeline = Deal::forCompany($id)->whereNotIn('stage', ['won', 'lost'])->sum('value');
        $won = Deal::forCompany($id)->where('stage', 'won')->sum('value');
        $tasks = CrmTask::forCompany($id)->with('assignee')->where('status', 'open')->orderBy('due_date')->limit(6)->get();
        $recent = Contact::forCompany($id)->latest()->limit(5)->get();
        $stages = Deal::forCompany($id)->selectRaw('stage, count(*) as total, sum(value) as value')->groupBy('stage')->get()->keyBy('stage');

        return view('crm.dashboard', compact('contacts', 'openDeals', 'pipeline', 'won', 'tasks', 'recent', 'stages'));
    }

    public function contacts(Request $r)
    {
        $contacts = Contact::forCompany(Workspace::id())
            ->when($r->filled('q'), fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$r->string('q').'%')->orWhere('email', 'like', '%'.$r->string('q').'%')->orWhere('organization', 'like', '%'.$r->string('q').'%')))
            ->when(in_array($r->input('status'), ['lead', 'customer', 'archived']), fn ($q) => $q->where('status', $r->input('status')))
            ->latest()->paginate(15)->withQueryString();

        return view('crm.contacts', compact('contacts'));
    }

    public function contactForm(?Contact $contact = null)
    {
        if ($contact) {
            Workspace::authorize($contact);
        }
        $contact ??= new Contact;

        return view('crm.contact-form', compact('contact'));
    }

    private function contactRules(?Contact $contact = null): array
    {
        return ['name' => 'required|string|max:120', 'email' => ['nullable', 'email', 'max:255', Rule::unique('contacts')->where('company_id', Workspace::id())->ignore($contact?->id)],
            'phone' => 'nullable|string|max:30', 'organization' => 'nullable|string|max:120', 'status' => 'required|in:lead,customer,archived', 'notes' => 'nullable|string|max:10000'];
    }

    public function saveContact(Request $r, ?Contact $contact = null)
    {
        if ($contact) {
            Workspace::authorize($contact);
        }
        $r->merge(['email' => $r->filled('email') ? strtolower($r->input('email')) : null]);
        $data = $r->validate($this->contactRules($contact));
        $contact ??= new Contact(['company_id' => Workspace::id()]);
        $contact->fill($data)->save();
        ActivityLogger::log('Contact saved', 'Contact #'.$contact->id, Workspace::id());

        return redirect()->route('crm.contacts')->with('success', 'Contact saved.');
    }

    public function deleteContact(Contact $contact)
    {
        Workspace::manager();
        Workspace::authorize($contact);
        $contact->delete();

        return back()->with('success', 'Contact removed.');
    }

    public function export()
    {
        Workspace::manager();
        $id = Workspace::id();
        ActivityLogger::log('Contacts exported', 'CSV export', $id);

        return response()->streamDownload(function () use ($id) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['name', 'email', 'phone', 'organization', 'status'], ',', '"', '');
            foreach (Contact::forCompany($id)->orderBy('id')->cursor() as $c) {
                $row = [$c->name, $c->email, $c->phone, $c->organization, $c->status];
                $row = array_map(fn ($v) => preg_match('/^[=+@\-\t\r\n]/', (string) $v) ? "'".$v : $v, $row);
                fputcsv($out, $row, ',', '"', '');
            } fclose($out);
        }, 'contacts.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $r)
    {
        Workspace::manager();
        $r->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);
        $f = fopen($r->file('file')->getRealPath(), 'r');
        $header = fgetcsv($f, 0, ',', '"', '');
        if ($header) {
            $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        }
        abort_unless($header === ['name', 'email', 'phone', 'organization', 'status'], 422, 'Use CSV headers: name,email,phone,organization,status');
        $rows = [];
        $seen = [];
        try {
            while (($row = fgetcsv($f, 0, ',', '"', '')) !== false) {
                if ($row === [null]) {
                    continue;
                }
                abort_if(count($rows) >= 500, 422, 'Import at most 500 contacts at a time.');
                abort_unless(count($row) === 5, 422, 'Every CSV row must have five columns.');
                $data = array_combine($header, $row);
                $data['email'] = $data['email'] !== '' ? strtolower(trim($data['email'])) : null;
                Validator::make($data, $this->contactRules())->validate();
                abort_if($data['email'] && isset($seen[$data['email']]), 422, 'Duplicate email in CSV.');
                if ($data['email']) {
                    $seen[$data['email']] = true;
                }
                $rows[] = $data;
            }
        } finally {
            fclose($f);
        }
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                Contact::create($row + ['company_id' => Workspace::id()]);
            }
            ActivityLogger::log('Contacts imported', count($rows).' contacts', Workspace::id());
        });

        return back()->with('success', count($rows).' contacts imported.');
    }

    public function deals(Request $r)
    {
        $deals = Deal::forCompany(Workspace::id())->with('contact', 'assignee')
            ->when($r->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$r->string('q').'%'))
            ->when(in_array($r->input('stage'), ['new', 'qualified', 'proposal', 'won', 'lost']), fn ($q) => $q->where('stage', $r->input('stage')))
            ->latest()->paginate(20)->withQueryString();

        return view('crm.deals', compact('deals'));
    }

    public function dealForm(?Deal $deal = null)
    {
        if ($deal) {
            Workspace::authorize($deal);
        }
        $deal ??= new Deal;
        $contacts = Contact::forCompany(Workspace::id())->orderBy('name')->get();
        $users = User::where('company_id', Workspace::id())->where('status', 'active')->get();

        return view('crm.deal-form', compact('deal', 'contacts', 'users'));
    }

    public function saveDeal(Request $r, ?Deal $deal = null)
    {
        if ($deal) {
            Workspace::authorize($deal);
        }
        $id = Workspace::id();
        $data = $r->validate(['title' => 'required|string|max:160', 'value' => 'required|numeric|min:0|max:999999999999',
            'stage' => 'required|in:new,qualified,proposal,won,lost', 'expected_close' => 'nullable|date',
            'contact_id' => ['nullable', Rule::exists('contacts', 'id')->where('company_id', $id)],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('company_id', $id)->where('status', 'active')],
            'notes' => 'nullable|string|max:10000']);
        $deal ??= new Deal(['company_id' => $id]);
        $deal->fill($data)->save();
        ActivityLogger::log('Deal saved', 'Deal #'.$deal->id, $id);

        return redirect()->route('crm.deals')->with('success', 'Deal saved.');
    }

    public function deleteDeal(Deal $deal)
    {
        Workspace::manager();
        Workspace::authorize($deal);
        $deal->delete();

        return back()->with('success', 'Deal removed.');
    }

    public function tasks()
    {
        $id = Workspace::id();
        $tasks = CrmTask::forCompany($id)->with('assignee', 'contact')->orderBy('status')->orderBy('due_date')->paginate(20);
        $users = User::where('company_id', $id)->where('status', 'active')->get();
        $contacts = Contact::forCompany($id)->orderBy('name')->get();

        return view('crm.tasks', compact('tasks', 'users', 'contacts'));
    }

    public function storeTask(Request $r)
    {
        $id = Workspace::id();
        $data = $r->validate(['title' => 'required|string|max:160', 'notes' => 'nullable|string|max:2000', 'due_date' => 'required|date',
            'assigned_to' => ['required', Rule::exists('users', 'id')->where('company_id', $id)->where('status', 'active')],
            'contact_id' => ['nullable', Rule::exists('contacts', 'id')->where('company_id', $id)]]);
        CrmTask::create($data + ['company_id' => $id]);

        return back()->with('success', 'Follow-up scheduled.');
    }

    public function updateTask(Request $r, CrmTask $task)
    {
        Workspace::authorize($task);
        $data = $r->validate(['status' => 'required|in:open,completed']);
        $task->update($data);

        return back()->with('success', 'Task updated.');
    }
}
