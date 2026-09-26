<aside class="sidebar" id="workspace-nav">
<a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark"><i class="bi bi-intersect"></i></span>CoreFlow<span style="font-size:10px;color:#8badb6;align-self:end;margin-bottom:4px">CRM</span></a>
<div class="nav-caption">WORKSPACE</div><nav aria-label="Main navigation">
@php
$items = auth()->user()->hasRole('Super Admin') ? [
['admin.dashboard','grid-1x2','Overview'],['admin.companies.index','buildings','Companies'],['admin.owners.index','person-badge','Owners'],
['admin.employees.index','people','Team members'],['admin.registrations.index','inbox','Requests'],['admin.billing','receipt','Billing'],
['admin.roles.index','shield-check','Roles'],['admin.activity_logs.index','clock-history','Activity']
] : [
['crm.dashboard','grid-1x2','Overview'],['crm.contacts','person-lines-fill','Contacts'],['crm.deals','kanban','Pipeline'],
['crm.tasks','check2-square','Tasks'],['crm.ai','stars','AI assistant'],['billing.index','receipt','Plan & billing'],
['workspace.settings','gear','Workspace settings']
];
@endphp
@foreach($items as [$route,$icon,$label])<a href="{{ route($route) }}" class="{{ request()->routeIs($route) ? 'active' : '' }}" @if(request()->routeIs($route))aria-current="page"@endif><i class="bi bi-{{ $icon }}"></i>{{ $label }}</a>@endforeach
@role('Company Admin')<div class="nav-caption">PEOPLE</div><a href="{{ route('company_admin.employees.index') }}"><i class="bi bi-people"></i>Team members</a><a href="{{ route('company_admin.invitations.create') }}"><i class="bi bi-person-plus"></i>Invitations</a>@endrole
</nav><div class="sidebar-foot"><div class="workspace-chip"><i class="bi bi-building me-2"></i>{{ auth()->user()->company?->name ?? 'Platform administration' }}</div>One workspace. Better relationships.</div></aside>
