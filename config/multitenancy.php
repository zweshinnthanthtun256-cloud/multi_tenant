<?php

// The application uses a shared database and company-scoped queries.
// Legacy tenant database names are retained as metadata; no database switching occurs.
return ['strategy' => 'shared_database', 'tenant_key' => 'company_id'];
