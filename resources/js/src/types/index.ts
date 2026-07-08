// Known permission strings from Role::permissions (see RoleSeeder/RoleFactory).
// UI must gate on these, never on Role.name, since role-to-permission
// mapping is expected to diverge between roles later.
export type Permission =
    | 'workers.view'
    | 'workers.manage'
    | 'clients.view'
    | 'clients.manage'
    | 'shifts.view'
    | 'shifts.manage'
    | 'allocation.run'
    | 'reports.view';

export interface Role {
    id: string;
    name: string;
    description: string | null;
    permissions: Permission[];
}

// Raw User model as returned by POST /login (role eager-loaded) — not a
// Resource, so this mirrors app/Models/User.php's fillable/visible fields.
export interface User {
    id: string;
    name: string;
    email: string;
    email_verified_at: string | null;
    role_id: string;
    role: Role;
    created_at: string;
    updated_at: string;
}

// Laravel's default paginator JSON shape, used by every index endpoint.
export interface PaginatedResponse<T> {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        links: { url: string | null; label: string; active: boolean }[];
        path: string;
        per_page: number;
        to: number | null;
        total: number;
    };
}
