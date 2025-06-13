<script setup lang="ts">
import AppPagination from '@/components/AppPagination.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, PaginatedResponse, User } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Briefcase, Mail, MapPin, User as UserIcon } from 'lucide-vue-next';

interface Props {
    users: PaginatedResponse<User>;
    auth: {
        user: User;
    };
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
];

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Users</h1>
                    <p class="text-sm text-muted-foreground">Manage system users and their information</p>
                </div>
            </div>

            <Card v-if="users.data.length > 0" class="overflow-hidden">
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border">
                            <thead class="bg-muted/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Contact</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Job Title</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border bg-background">
                                <tr v-for="user in users.data" :key="user.id" class="transition-colors hover:bg-muted/50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <Avatar class="h-10 w-10 flex-shrink-0">
                                                <AvatarFallback class="bg-primary text-primary-foreground">
                                                    {{ getInitials(user.name) }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-foreground">
                                                    {{ user.name }}
                                                </div>
                                                <div class="text-sm text-muted-foreground">
                                                    {{ user.employee_ref || `#${user.id}` }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center text-sm text-foreground">
                                            <Mail class="mr-2 h-4 w-4 text-muted-foreground" />
                                            {{ user.email }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div v-if="user.department" class="flex items-center text-sm text-foreground">
                                            <MapPin class="mr-2 h-4 w-4 text-muted-foreground" />
                                            {{ user.department }}
                                        </div>
                                        <Badge v-else variant="secondary" class="text-xs"> N/A </Badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div v-if="user.job_title" class="flex items-center text-sm text-foreground">
                                            <Briefcase class="mr-2 h-4 w-4 text-muted-foreground" />
                                            {{ user.job_title }}
                                        </div>
                                        <Badge v-else variant="secondary" class="text-xs"> N/A </Badge>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <AppPagination v-if="users.last_page > 1" :links="users.links" :current-page="users.current_page" :last-page="users.last_page">
                Showing {{ users.from }} to {{ users.to }} of {{ users.total }} users
            </AppPagination>

            <!-- Empty State -->
            <Card v-if="users.data.length === 0" class="flex items-center justify-center py-16">
                <div class="text-center">
                    <UserIcon class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                    <h3 class="mb-2 text-lg font-medium text-foreground">No users found</h3>
                    <p class="text-muted-foreground">No users have been created yet.</p>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
