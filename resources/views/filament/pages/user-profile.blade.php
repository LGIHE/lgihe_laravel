<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Profile Information Section -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Profile Information
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Update your account's profile information and email address.
                </p>
            </div>
            <div class="px-6 py-4">
                <form wire:submit="updateProfile">
                    {{ $this->profileForm }}
                    
                    <div class="mt-6">
                        <x-filament::button type="submit" color="primary">
                            Update Profile
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password Update Section -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Update Password
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Ensure your account is using a long, random password to stay secure.
                </p>
            </div>
            <div class="px-6 py-4">
                <form wire:submit="updatePassword">
                    {{ $this->passwordForm }}
                    
                    <div class="mt-6">
                        <x-filament::button type="submit" color="warning">
                            Update Password
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Information -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Account Information
                </h3>
            </div>
            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Account Created
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ auth()->user()->created_at->format('F j, Y') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Last Updated
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ auth()->user()->updated_at->format('F j, Y g:i A') }}
                        </dd>
                    </div>
                    @if(auth()->user()->getRoleNames()->isNotEmpty())
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Roles
                        </dt>
                        <dd class="mt-1">
                            @foreach(auth()->user()->getRoleNames() as $role)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mr-2">
                                    {{ $role }}
                                </span>
                            @endforeach
                        </dd>
                    </div>
                    @endif
                    @if(auth()->user()->getAllPermissions()->isNotEmpty())
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Permissions
                        </dt>
                        <dd class="mt-1">
                            <div class="flex flex-wrap gap-1">
                                @foreach(auth()->user()->getAllPermissions()->take(10) as $permission)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        {{ $permission->name }}
                                    </span>
                                @endforeach
                                @if(auth()->user()->getAllPermissions()->count() > 10)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        +{{ auth()->user()->getAllPermissions()->count() - 10 }} more
                                    </span>
                                @endif
                            </div>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</x-filament-panels::page>