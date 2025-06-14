import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
    can: Record<string, boolean>;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
    show?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    can: Record<string, boolean>;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    employee_ref: string | null;
    department: string | null;
    job_title: string | null;
}

export type BreadcrumbItemType = BreadcrumbItem;


export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    links: PaginationLink[];
    from: number;
    to: number;
    total: number;
    per_page: number;
}

export interface Campaign {
    id: number;
    title: string;
    slug: string;
    featured_image_url?: string;
    description: string;
    goal_amount_cents: number;
    current_amount_cents: number;
    donations_count: number;
    donors_count: number;
    status: 'active' | 'completed' | 'cancelled' | string;
    start_date: string;
    end_date?: string;
    featured_image_url?: string;
    creator_id: number;
    creator: User;
    category: Category;
    donations: Donation[];
    created_at: string;
}

export interface Category {
    id: number;
    name: string;
}

export interface DateValue {
    year: number;
    month: number;
    day: number;
}

export interface Donation {
    id: number;
    amount_cents: number;
    status: string;
    payment_method: string;
    transaction_id: string;
    message?: string;
    created_at: string;
    completed_at?: string;
    is_anonymous: boolean;
    user?: User;
    campaign?: Campaign;
}