<div class="flex grow flex-col md:gap-y-5 gap-y-2 overflow-y-auto bg-rich-black ">
    <div class="flex w-full md:shrink-0 items-center justify-center pt-4">
        <x-application-logo class="md:h-full h-48"/>
    </div>
    <nav class="flex flex-1 flex-col ml-6 pb-4">
        <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
                <ul role="list" class="-ml-2 space-y-1">
                    <li>
                        <a href="{{ route('dashboard' )}}" class="{{ request()->routeIs('dashboard') ? 'text-rich-black bg-anti-flash-white' : 'text-white hover:text-rich-black hover:bg-anti-flash-white' }} group flex gap-x-3 p-2 text-sm leading-6 font-semibold" x-state:on="Current" x-state:off="Default" x-state-description="Current: &quot;bg-gray-50 text-rich-black&quot;, Default: &quot;text-white hover:text-rich-black hover:bg-anti-flash-white&quot;">
                            <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('dashboard') ? 'text-xanthous' : 'text-tawny group-hover:text-xanthous' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"></path>
                            </svg>
                            {{ __('Dashboard') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('recipes.index') }}" class="{{ request()->routeIs('recipes.*') ? 'text-rich-black bg-anti-flash-white' : 'text-white hover:text-rich-black hover:bg-anti-flash-white' }} group flex gap-x-3 p-2 text-sm leading-6 font-semibold" x-state-description="undefined: &quot;bg-gray-50 text-rich-black&quot;, undefined: &quot;text-white hover:text-rich-black hover:bg-anti-flash-white&quot;">
                            <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('recipes.*') ? 'text-xanthous' : 'text-tawny group-hover:text-xanthous' }}" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 256 256">
                                <path fill="currentColor" d="M104 104v80a8 8 0 0 1-16 0v-80a8 8 0 0 1 16 0Zm40-8a8 8 0 0 0-8 8v80a8 8 0 0 0 16 0v-80a8 8 0 0 0-8-8Zm96 16v64a24 24 0 0 1-24 24h-16v8a16 16 0 0 1-16 16H56a16 16 0 0 1-16-16V72c0-30.88 28.71-56 64-56c16.77 0 32.91 5.8 44.82 16H160a40 40 0 0 1 40 40v16h16a24 24 0 0 1 24 24ZM57 64h125.62A24 24 0 0 0 160 48h-14.26a8 8 0 0 1-5.53-2.22C131.06 37 117.87 32 104 32c-23.18 0-42.57 13.76-47 32Zm127 144V80H56v128h128Zm40-96a8 8 0 0 0-8-8h-16v80h16a8 8 0 0 0 8-8Z" />
                            </svg>
                            {{ __('Recipes') }}
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-white hover:text-rich-black hover:bg-anti-flash-white group flex gap-x-3 p-2 text-sm leading-6 font-semibold" x-state-description="undefined: &quot;bg-gray-50 text-rich-black&quot;, undefined: &quot;text-white hover:text-rich-black hover:bg-anti-flash-white&quot;">
                            <svg class="h-6 w-6 shrink-0 text-tawny group-hover:text-xanthous" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path>
                            </svg>
                            Calendar
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-white hover:text-rich-black hover:bg-anti-flash-white group flex gap-x-3 p-2 text-sm leading-6 font-semibold" x-state-description="undefined: &quot;bg-gray-50 text-rich-black&quot;, undefined: &quot;text-white hover:text-rich-black hover:bg-anti-flash-white&quot;">
                            <svg class="h-6 w-6 shrink-0 text-tawny group-hover:text-xanthous" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z"></path>
                            </svg>
                            Reports
                        </a>
                    </li>

                </ul>
            </li>
            <li class="mt-auto">
                <ul role="list" class="-ml-2 mt-2 space-y-1">
                    <li>
                        <a href="#" class="text-white hover:text-rich-black hover:bg-anti-flash-white group flex gap-x-3 p-2 text-sm leading-6 font-semibold" x-state:on="Current" x-state:off="Default" x-state-description="Current: &quot;bg-gray-50 text-rich-black&quot;, Default: &quot;text-white hover:text-rich-black hover:bg-anti-flash-white&quot;">
                            <svg class="h-6 w-6 shrink-0 text-tawny group-hover:text-xanthous" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                            {{ __('Notification Center') }}
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-white hover:text-rich-black hover:bg-anti-flash-white group flex gap-x-3 p-2 text-sm leading-6 font-semibold" x-state:on="Current" x-state:off="Default" x-state-description="Current: &quot;bg-gray-50 text-rich-black&quot;, Default: &quot;text-white hover:text-rich-black hover:bg-anti-flash-white&quot;">
                            <svg class="h-6 w-6 shrink-0 text-tawny group-hover:text-xanthous" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M10 4a4 4 0 0 0-4 4a4 4 0 0 0 4 4a4 4 0 0 0 4-4a4 4 0 0 0-4-4m7 8a.26.26 0 0 0-.26.21l-.19 1.32c-.3.13-.59.29-.85.47l-1.24-.5c-.11 0-.24 0-.31.13l-1 1.73c-.06.11-.04.24.06.32l1.06.82a4.193 4.193 0 0 0 0 1l-1.06.82a.26.26 0 0 0-.06.32l1 1.73c.06.13.19.13.31.13l1.24-.5c.26.18.54.35.85.47l.19 1.32c.02.12.12.21.26.21h2c.11 0 .22-.09.24-.21l.19-1.32c.3-.13.57-.29.84-.47l1.23.5c.13 0 .26 0 .33-.13l1-1.73a.26.26 0 0 0-.06-.32l-1.07-.82c.02-.17.04-.33.04-.5c0-.17-.01-.33-.04-.5l1.06-.82a.26.26 0 0 0 .06-.32l-1-1.73c-.06-.13-.19-.13-.32-.13l-1.23.5c-.27-.18-.54-.35-.85-.47l-.19-1.32A.236.236 0 0 0 19 12h-2m-7 2c-4.42 0-8 1.79-8 4v2h9.68a7 7 0 0 1-.68-3a7 7 0 0 1 .64-2.91c-.53-.06-1.08-.09-1.64-.09m8 1.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5c-.84 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5Z" />
                            </svg>
                            {{ __('Profil') }}
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</div>
