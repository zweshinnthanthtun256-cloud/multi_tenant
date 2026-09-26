<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Deal;
use App\Support\Workspace;
use Illuminate\Http\Request;

class WorkspaceSettingsController extends Controller
{
    public function index()
    {
        $company = Company::findOrFail(Workspace::id());
        $logs = ActivityLog::where('company_id', $company->id)->latest()->paginate(15);

        return view('crm.settings', compact('company', 'logs'));
    }

    public function update(Request $r)
    {
        abort_unless(auth()->user()->hasRole('Company Admin'), 403);
        $data = $r->validate(['phone' => 'nullable|string|max:30', 'website' => 'nullable|url|max:255', 'address' => 'nullable|string|max:500', 'description' => 'nullable|string|max:2000']);
        Company::findOrFail(Workspace::id())->update($data);

        return back()->with('success', 'Workspace settings saved.');
    }

    public function export()
    {
        abort_unless(auth()->user()->hasRole('Company Admin'), 403);
        $id = Workspace::id();

        return response()->streamDownload(function () use ($id) {
            echo '{"contacts":';
            $this->streamRows(Contact::forCompany($id)->orderBy('id')->cursor());
            echo ',"deals":';
            $this->streamRows(Deal::forCompany($id)->orderBy('id')->cursor());
            echo ',"tasks":';
            $this->streamRows(CrmTask::forCompany($id)->orderBy('id')->cursor());
            echo '}';
        }, 'workspace-export.json', ['Content-Type' => 'application/json']);
    }

    private function streamRows($rows): void
    {
        echo '[';
        $first = true;
        foreach ($rows as $row) {
            if (! $first) {
                echo ',';
            } echo json_encode($row, JSON_THROW_ON_ERROR);
            $first = false;
        }
        echo ']';
    }
}
