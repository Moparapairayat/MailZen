{{-- Sidebar (Admin) --}}
<aside
    data-sidebar="app"
    class="bg-white dark:bg-admin-sidebar fixed inset-y-0 left-0 z-40 h-screen w-64 border-r border-gray-100 dark:border-admin-border flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-200"
    :class="sidebarOpen ? 'translate-x-0' : ''"
>
    <div class="h-full">
        <div class="flex flex-col items-start justify-between p-4 relative h-full">
            <div class="flex flex-col gap-8 items-start relative w-full flex-1 min-h-0">
                <div class="flex items-start justify-between w-full relative">
                    {{-- Logo --}}
                    @php
                        use Illuminate\Support\Facades\Storage;

                        try {
                            $appLogo = \App\Models\Setting::get('app_logo');
                            $appLogoDark = \App\Models\Setting::get('app_logo_dark');
                        } catch (\Throwable $e) {
                            $appLogo = null;
                            $appLogoDark = null;
                        }

                        $hasLogo = is_string($appLogo) && trim($appLogo) !== '';
                        $hasLogoDark = is_string($appLogoDark) && trim($appLogoDark) !== '';
                    @endphp

                    @if($hasLogo)
                        <img
                            src="{{ (string) config('filesystems.branding_disk', 'public') === 'public' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($appLogo, '/')) : Storage::disk((string) config('filesystems.branding_disk', 'public'))->url($appLogo) }}"
                            alt="{{ __('App Logo') }}"
                            class="block dark:hidden h-auto object-contain w-[150px] mx-3 mt-3" 
                        />

                        @if($hasLogoDark)
                            <img
                                src="{{ (string) config('filesystems.branding_disk', 'public') === 'public' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($appLogoDark, '/')) : Storage::disk((string) config('filesystems.branding_disk', 'public'))->url($appLogoDark) }}"
                                alt="{{ __('App Logo') }}"
                                class="hidden dark:block h-auto object-contain w-[150px] mx-3 mt-3" 
                            />
                        @endif
                    @else
                        <div class="mx-3">
                            <svg width="130" height="30" viewBox="0 0 220 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M28.292 1.32781L28.7452 2.25219C28.9718 2.70628 29.5383 3.12793 30.04 3.22523L30.6551 3.32254C32.5002 3.63066 32.9372 4.99292 31.61 6.33895L31.0435 6.90656C30.6713 7.29577 30.4608 8.04177 30.5741 8.56072L30.6551 8.90127C31.1568 11.1393 29.9753 11.9988 28.033 10.8311L27.6122 10.5879C27.1105 10.296 26.3012 10.296 25.7995 10.5879L25.3786 10.8311C23.4202 12.015 22.2387 11.1393 22.7566 8.90127L22.8375 8.56072C22.9508 8.04177 22.7404 7.29577 22.3682 6.90656L21.8017 6.32273C20.4745 4.9767 20.9115 3.61445 22.7566 3.30632L23.3716 3.20901C23.8572 3.12793 24.4399 2.69006 24.6665 2.23598L25.1197 1.31159C25.9937 -0.439891 27.418 -0.439891 28.292 1.32781Z" fill="#1E5FEA"/>
                                <path d="M31.6748 13.1178C31.0921 13.5395 29.4574 14.3179 27.1267 13.1178C26.8677 12.9881 26.544 12.9719 26.2851 13.1178C25.3625 13.5881 24.5209 13.7827 23.8573 13.7827C22.8214 13.7827 22.0931 13.3773 21.737 13.1178C21.1381 12.6799 19.8757 11.4312 20.3127 8.78779C20.3612 8.5121 20.2803 8.2364 20.1023 8.02558C18.9855 6.74441 18.3704 5.02538 18.8074 3.67935C18.9693 3.14418 18.6294 2.43062 18.0791 2.43062H8.0927C3.23708 2.43062 0 4.86321 0 10.5393V21.8914C0 27.5674 3.23708 30 8.0927 30H24.2781C29.1337 30 32.3708 27.5674 32.3708 21.8914V13.4097C32.3708 13.1016 31.9338 12.9394 31.6748 13.1178ZM19.9728 16.3613C18.9045 17.2208 17.545 17.6424 16.1854 17.6424C14.8258 17.6424 13.4501 17.2208 12.398 16.3613L7.33198 12.3069C6.81405 11.8853 6.73312 11.1069 7.13776 10.5879C7.55858 10.069 8.31929 9.97165 8.83722 10.3933L13.9032 14.4476C15.1333 15.4369 17.2213 15.4369 18.4513 14.4476C18.9693 14.026 19.73 14.1071 20.1508 14.6422C20.5878 15.1612 20.5069 15.9396 19.9728 16.3613Z" fill="#1E5FEA"/>
                                <path d="M44.8093 28.3274V2.03285H51.7833L58.3619 17.7375H59.009L65.5516 2.03285H72.6334V28.3274H67.5647V5.88697L68.2118 5.95901L61.1299 22.9604H55.9174L48.7996 5.95901L49.4826 5.88697V28.3274H44.8093Z" fill="#1E5FEA"/>
                                <path d="M89.4609 28.3274V22.5282H88.6341V16.0806C88.6341 14.952 88.3585 14.1115 87.8073 13.5592C87.2561 13.0069 86.4053 12.7307 85.255 12.7307C84.6558 12.7307 83.9368 12.7428 83.098 12.7668C82.2592 12.7908 81.4085 12.8268 80.5457 12.8748C79.7069 12.8988 78.952 12.9349 78.281 12.9829V8.73254C78.8322 8.68452 79.4553 8.63649 80.1503 8.58846C80.8453 8.54044 81.5523 8.51642 82.2712 8.51642C83.0142 8.49241 83.7092 8.4804 84.3563 8.4804C86.3694 8.4804 88.035 8.74455 89.3531 9.27284C90.6952 9.80113 91.7017 10.6296 92.3727 11.7582C93.0678 12.8868 93.4152 14.3637 93.4152 16.1887V28.3274H89.4609ZM83.17 28.8316C81.756 28.8316 80.5098 28.5795 79.4313 28.0752C78.3768 27.5709 77.55 26.8505 76.9509 25.914C76.3757 24.9775 76.0881 23.8489 76.0881 22.5282C76.0881 21.0874 76.4356 19.9107 77.1306 18.9982C77.8496 18.0857 78.8442 17.4013 80.1143 16.9451C81.4085 16.4888 82.9183 16.2607 84.6438 16.2607H89.1733V19.2503H84.5719C83.4216 19.2503 82.5349 19.5385 81.9118 20.1148C81.3126 20.6671 81.013 21.3875 81.013 22.276C81.013 23.1645 81.3126 23.8849 81.9118 24.4372C82.5349 24.9895 83.4216 25.2657 84.5719 25.2657C85.2669 25.2657 85.902 25.1456 86.4772 24.9055C87.0764 24.6413 87.5676 24.2091 87.9511 23.6088C88.3585 22.9844 88.5862 22.1439 88.6341 21.0874L89.8564 22.4921C89.7365 23.8609 89.401 25.0135 88.8498 25.95C88.3226 26.8866 87.5796 27.607 86.621 28.1112C85.6863 28.5915 84.536 28.8316 83.17 28.8316Z" fill="#1E5FEA"/>
                                <path d="M98.6238 28.3274V8.76856H103.621V28.3274H98.6238ZM95.8918 12.5146V8.76856H103.621V12.5146H95.8918ZM100.457 6.42727C99.4746 6.42727 98.7437 6.17513 98.2644 5.67085C97.809 5.14256 97.5813 4.4942 97.5813 3.72578C97.5813 2.90933 97.809 2.24896 98.2644 1.74469C98.7437 1.24041 99.4746 0.988267 100.457 0.988267C101.44 0.988267 102.159 1.24041 102.614 1.74469C103.069 2.24896 103.297 2.90933 103.297 3.72578C103.297 4.4942 103.069 5.14256 102.614 5.67085C102.159 6.17513 101.44 6.42727 100.457 6.42727Z" fill="#1E5FEA"/>
                                <path d="M108.553 28.3274V2.03285H113.549V28.3274H108.553ZM106.108 5.77891V2.03285H113.549V5.77891H106.108Z" fill="#1E5FEA"/>
                                <path d="M129.637 20.2589V15.8285H134.741C135.772 15.8285 136.635 15.6243 137.33 15.2161C138.049 14.8079 138.588 14.2436 138.947 13.5232C139.331 12.8028 139.522 11.9743 139.522 11.0378C139.522 10.1013 139.331 9.27284 138.947 8.55244C138.588 7.83204 138.049 7.27974 137.33 6.89553C136.635 6.4873 135.772 6.28319 134.741 6.28319H129.637V1.85275H134.31C136.515 1.85275 138.384 2.21294 139.918 2.93334C141.476 3.65374 142.662 4.68631 143.477 6.03105C144.292 7.35178 144.699 8.92465 144.699 10.7497V11.326C144.699 13.151 144.292 14.7359 143.477 16.0806C142.662 17.4013 141.476 18.4339 139.918 19.1783C138.384 19.8987 136.515 20.2589 134.31 20.2589H129.637ZM125.251 28.3274V1.85275H130.356V28.3274H125.251Z" fill="#241D1D"/>
                                <path d="M154.089 28.9397C151.836 28.9397 150.087 28.1953 148.84 26.7065C147.618 25.2176 147.007 23.0084 147.007 20.0788V8.73254H152.004V20.511C152.004 21.7117 152.339 22.6722 153.01 23.3926C153.681 24.089 154.592 24.4372 155.743 24.4372C156.893 24.4372 157.827 24.065 158.546 23.3206C159.289 22.5762 159.661 21.5676 159.661 20.2949V8.73254H164.658V28.3274H160.703V20.0068H161.099C161.099 21.9759 160.847 23.6208 160.344 24.9415C159.841 26.2622 159.086 27.2588 158.079 27.9311C157.073 28.6035 155.814 28.9397 154.305 28.9397H154.089Z" fill="#241D1D"/>
                                <path d="M169.319 28.3274V8.76856H173.274V17.0531H173.166C173.166 14.2436 173.765 12.1184 174.963 10.6776C176.161 9.23682 177.923 8.51642 180.248 8.51642H180.966V12.8748H179.6C177.923 12.8748 176.617 13.3311 175.682 14.2436C174.771 15.1321 174.316 16.4288 174.316 18.1337V28.3274H169.319Z" fill="#241D1D"/>
                                <path d="M190.313 28.9397C187.629 28.9397 185.52 28.3754 183.986 27.2468C182.476 26.0941 181.685 24.5092 181.613 22.4921H186.107C186.179 23.1645 186.55 23.7768 187.221 24.3291C187.892 24.8815 188.947 25.1576 190.385 25.1576C191.583 25.1576 192.53 24.9415 193.225 24.5092C193.944 24.077 194.303 23.4887 194.303 22.7443C194.303 22.0959 194.027 21.5796 193.476 21.1954C192.949 20.8112 192.038 20.5591 190.744 20.439L189.019 20.2589C186.838 20.0188 185.16 19.3944 183.986 18.3859C182.812 17.3773 182.224 16.0326 182.224 14.3516C182.224 13.0069 182.56 11.8783 183.231 10.9658C183.902 10.0533 184.825 9.36889 185.999 8.91264C187.197 8.43238 188.563 8.19224 190.097 8.19224C192.494 8.19224 194.435 8.72054 195.921 9.77712C197.407 10.8337 198.186 12.3826 198.257 14.4237H193.764C193.716 13.7513 193.38 13.175 192.757 12.6947C192.134 12.2145 191.235 11.9743 190.061 11.9743C189.007 11.9743 188.192 12.1784 187.617 12.5867C187.041 12.9949 186.754 13.5232 186.754 14.1715C186.754 14.7959 186.982 15.2762 187.437 15.6123C187.916 15.9485 188.683 16.1767 189.738 16.2967L191.463 16.4768C193.764 16.7169 195.561 17.3533 196.855 18.3859C198.174 19.4184 198.833 20.8112 198.833 22.5642C198.833 23.8609 198.473 24.9895 197.754 25.95C197.059 26.8866 196.077 27.619 194.806 28.1473C193.536 28.6755 192.038 28.9397 190.313 28.9397Z" fill="#241D1D"/>
                                <path d="M210.617 29.0117C208.94 29.0117 207.466 28.7236 206.196 28.1473C204.95 27.5709 203.907 26.8025 203.068 25.842C202.253 24.8574 201.63 23.7648 201.199 22.5642C200.792 21.3635 200.588 20.1388 200.588 18.8901V18.2058C200.588 16.9091 200.792 15.6604 201.199 14.4597C201.63 13.235 202.253 12.1544 203.068 11.2179C203.907 10.2574 204.938 9.50097 206.16 8.94866C207.382 8.37234 208.796 8.08418 210.402 8.08418C212.511 8.08418 214.272 8.55244 215.686 9.48896C217.124 10.4015 218.203 11.6141 218.922 13.127C219.641 14.6158 220 16.2247 220 17.9536V19.7546H202.709V16.6929H216.872L215.327 18.2058C215.327 16.9571 215.147 15.8885 214.787 15C214.428 14.1115 213.877 13.4271 213.134 12.9469C212.415 12.4666 211.504 12.2265 210.402 12.2265C209.299 12.2265 208.365 12.4786 207.598 12.9829C206.831 13.4872 206.244 14.2196 205.836 15.1801C205.453 16.1166 205.261 17.2452 205.261 18.566C205.261 19.7906 205.453 20.8832 205.836 21.8438C206.22 22.7803 206.807 23.5247 207.598 24.077C208.389 24.6053 209.395 24.8694 210.617 24.8694C211.84 24.8694 212.834 24.6293 213.601 24.149C214.368 23.6448 214.859 23.0324 215.075 22.312H219.676C219.389 23.6568 218.838 24.8334 218.023 25.842C217.208 26.8505 216.166 27.631 214.895 28.1833C213.649 28.7356 212.223 29.0117 210.617 29.0117Z" fill="#241D1D"/>
                            </svg>
                        </div>

                    @endif

                    <button
                        type="button"
                        class="lg:hidden absolute top-0 right-0 p-2 rounded-md text-admin-text-secondary hover:text-admin-text-primary hover:bg-white/5"
                        @click="sidebarOpen = false"
                        aria-label="{{ __('Close sidebar') }}"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex flex-col gap-6 items-start relative w-full flex-1 min-h-0 overflow-y-auto">
                    <div class="flex flex-col gap-3 items-start relative shrink-0 w-full">
                        <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px] ml-3">{{ __('General') }}</p>

                        <div class="flex flex-col items-start relative shrink-2 w-full">
                            <a href="{{ route('admin.dashboard') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.dashboard') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M7.3125 2.25H4.3125C3.78916 2.25 3.5275 2.25 3.31457 2.31459C2.83517 2.46001 2.46001 2.83517 2.31459 3.31457C2.25 3.5275 2.25 3.78917 2.25 4.3125C2.25 4.83584 2.25 5.0975 2.31459 5.31043C2.46001 5.78983 2.83517 6.16499 3.31457 6.31041C3.5275 6.375 3.78916 6.375 4.3125 6.375H7.3125C7.83585 6.375 8.09752 6.375 8.31045 6.31041C8.78985 6.16499 9.165 5.78983 9.31042 5.31043C9.375 5.0975 9.375 4.83584 9.375 4.3125C9.375 3.78917 9.375 3.5275 9.31042 3.31457C9.165 2.83517 8.78985 2.46001 8.31045 2.31459C8.09752 2.25 7.83585 2.25 7.3125 2.25Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                        <path d="M15.75 7.3125V4.3125C15.75 3.78916 15.75 3.5275 15.6854 3.31457C15.54 2.83517 15.1649 2.46001 14.6855 2.31459C14.4725 2.25 14.2109 2.25 13.6875 2.25C13.1642 2.25 12.9025 2.25 12.6896 2.31459C12.2102 2.46001 11.835 2.83517 11.6896 3.31457C11.625 3.5275 11.625 3.78916 11.625 4.3125V7.3125C11.625 7.83585 11.625 8.09752 11.6896 8.31045C11.835 8.78985 12.2102 9.165 12.6896 9.31042C12.9025 9.375 13.1642 9.375 13.6875 9.375C14.2109 9.375 14.4725 9.375 14.6855 9.31042C15.1649 9.165 15.54 8.78985 15.6854 8.31045C15.75 8.09752 15.75 7.83585 15.75 7.3125Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                        <path d="M12.6896 15.6854C12.9025 15.75 13.1642 15.75 13.6875 15.75C14.2109 15.75 14.4725 15.75 14.6855 15.6854C15.1649 15.54 15.54 15.1649 15.6854 14.6855C15.75 14.4725 15.75 14.2109 15.75 13.6875C15.75 13.1642 15.75 12.9025 15.6854 12.6896C15.54 12.2102 15.1649 11.835 14.6855 11.6896C14.4725 11.625 14.2109 11.625 13.6875 11.625C13.1642 11.625 12.9025 11.625 12.6896 11.6896C12.2102 11.835 11.835 12.2102 11.6896 12.6896C11.625 12.9025 11.625 13.1642 11.625 13.6875C11.625 14.2109 11.625 14.4725 11.6896 14.6855C11.835 15.1649 12.2102 15.54 12.6896 15.6854Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                        <path d="M6.375 8.625H5.25C3.83578 8.625 3.12868 8.625 2.68934 9.06435C2.25 9.5037 2.25 10.2108 2.25 11.625V12.75C2.25 14.1642 2.25 14.8713 2.68934 15.3106C3.12868 15.75 3.83578 15.75 5.25 15.75H6.375C7.7892 15.75 8.4963 15.75 8.93565 15.3106C9.375 14.8713 9.375 14.1642 9.375 12.75V11.625C9.375 10.2108 9.375 9.5037 8.93565 9.06435C8.4963 8.625 7.7892 8.625 6.375 8.625Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Dashboard') }}</p>
                            </a>

                            <a href="{{ route('admin.users.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.users.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M12.375 15V13.4778C12.375 12.5461 11.9555 11.6324 11.1077 11.246C10.0736 10.7746 8.83342 10.5 7.5 10.5C6.16659 10.5 4.92638 10.7746 3.89226 11.246C3.04445 11.6324 2.625 12.5461 2.625 13.4778V15" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M15.375 15.0007V13.4785C15.375 12.5467 14.9555 11.6332 14.1077 11.2467C13.9123 11.1576 13.7094 11.0755 13.5 11.001" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M7.5 8.25C8.94975 8.25 10.125 7.07475 10.125 5.625C10.125 4.17525 8.94975 3 7.5 3C6.05025 3 4.875 4.17525 4.875 5.625C4.875 7.07475 6.05025 8.25 7.5 8.25Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M11.25 3.1084C12.3343 3.43111 13.125 4.43556 13.125 5.62469C13.125 6.81383 12.3343 7.8183 11.25 8.14103" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Users') }}</p>
                            </a>

                            @admincan('admin.support_tickets.access')
                                <a href="{{ route('admin.support-tickets.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.support-tickets.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                    <div class="relative shrink-0 w-[18px] h-[18px]">
                                        <svg class="block w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a10.94 10.94 0 01-4-.73L3 20l1.46-3.65A7.92 7.92 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </div>
                                    <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Support') }}</p>
                                </a>
                            @endadmincan

                            @admincan('admin.blog_posts.access')
                                <a href="{{ route('admin.blog-posts.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.blog-posts.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                    <div class="relative shrink-0 w-[18px] h-[18px]">
                                        <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                            <path d="M3 3.75C3 2.92157 3.67157 2.25 4.5 2.25H13.5C14.3284 2.25 15 2.92157 15 3.75V14.25C15 15.0784 14.3284 15.75 13.5 15.75H4.5C3.67157 15.75 3 15.0784 3 14.25V3.75Z" stroke="currentColor" stroke-width="1.125" />
                                            <path d="M5.25 5.25H12.75" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            <path d="M5.25 8.25H12" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            <path d="M5.25 11.25H10.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                        </svg>
                                    </div>
                                    <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Blog') }}</p>
                                </a>
                            @endadmincan

                            @admincan('admin.settings.access')
                                <a href="{{ route('admin.site-pages.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ (request()->routeIs('admin.site-pages.*') || request()->routeIs('admin.homepages.*')) ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                    <div class="relative shrink-0 w-[18px] h-[18px]">
                                        <svg class="block w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4h10M7 8h10M7 12h10M7 16h10M7 20h10" />
                                        </svg>
                                    </div>
                                    <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Pages') }}</p>
                                </a>
                            @endadmincan

                            @admincan('admin.ai_tools.access')
                                <a href="{{ route('admin.ai-tools.dashboard') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.ai-tools.dashboard') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                    <div class="relative shrink-0 w-[18px] h-[18px]">
                                        <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                            <path d="M7.3125 2.25H4.3125C3.78916 2.25 3.5275 2.25 3.31457 2.31459C2.83517 2.46001 2.46001 2.83517 2.31459 3.31457C2.25 3.5275 2.25 3.78917 2.25 4.3125C2.25 4.83584 2.25 5.0975 2.31459 5.31043C2.46001 5.78983 2.83517 6.16499 3.31457 6.31041C3.5275 6.375 3.78916 6.375 4.3125 6.375H7.3125C7.83585 6.375 8.09752 6.375 8.31045 6.31041C8.78985 6.16499 9.165 5.78983 9.31042 5.31043C9.375 5.0975 9.375 4.83584 9.375 4.3125C9.375 3.78917 9.375 3.5275 9.31042 3.31457C9.165 2.83517 8.78985 2.46001 8.31045 2.31459C8.09752 2.25 7.83585 2.25 7.3125 2.25Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                            <path d="M15.75 7.3125V4.3125C15.75 3.78916 15.75 3.5275 15.6854 3.31457C15.54 2.83517 15.1649 2.46001 14.6855 2.31459C14.4725 2.25 14.2109 2.25 13.6875 2.25C13.1642 2.25 12.9025 2.25 12.6896 2.31459C12.2102 2.46001 11.835 2.83517 11.6896 3.31457C11.625 3.5275 11.625 3.78916 11.625 4.3125V7.3125C11.625 7.83585 11.625 8.09752 11.6896 8.31045C11.835 8.78985 12.2102 9.165 12.6896 9.31042C12.9025 9.375 13.1642 9.375 13.6875 9.375C14.2109 9.375 14.4725 9.375 14.6855 9.31042C15.1649 9.165 15.54 8.78985 15.6854 8.31045C15.75 8.09752 15.75 7.83585 15.75 7.3125Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                            <path d="M12.6896 15.6854C12.9025 15.75 13.1642 15.75 13.6875 15.75C14.2109 15.75 14.4725 15.75 14.6855 15.6854C15.1649 15.54 15.54 15.1649 15.6854 14.6855C15.75 14.4725 15.75 14.2109 15.75 13.6875C15.75 13.1642 15.75 12.9025 15.6854 12.6896C15.54 12.2102 15.1649 11.835 14.6855 11.6896C14.4725 11.625 14.2109 11.625 13.6875 11.625C13.1642 11.625 12.9025 11.625 12.6896 11.6896C12.2102 11.835 11.835 12.2102 11.6896 12.6896C11.625 12.9025 11.625 13.1642 11.625 13.6875C11.625 14.2109 11.625 14.4725 11.6896 14.6855C11.835 15.1649 12.2102 15.54 12.6896 15.6854Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                            <path d="M6.375 8.625H5.25C3.83578 8.625 3.12868 8.625 2.68934 9.06435C2.25 9.5037 2.25 10.2108 2.25 11.625V12.75C2.25 14.1642 2.25 14.8713 2.68934 15.3106C3.12868 15.75 3.83578 15.75 5.25 15.75H6.375C7.7892 15.75 8.4963 15.75 8.93565 15.3106C9.375 14.8713 9.375 14.1642 9.375 12.75V11.625C9.375 10.2108 9.375 9.5037 8.93565 9.06435C8.4963 8.625 7.7892 8.625 6.375 8.625Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.25" />
                                        </svg>
                                    </div>
                                    <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('AI Dashboard') }}</p>
                                </a>
                                <a href="{{ route('admin.ai-tools.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ (request()->routeIs('admin.ai-tools.*') && !request()->routeIs('admin.ai-tools.dashboard')) ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                    <div class="relative shrink-0 w-[18px] h-[18px]">
                                        <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                            <path d="M9 1.5C5.27208 1.5 2.25 4.52208 2.25 8.25C2.25 11.9779 5.27208 15 9 15C12.7279 15 15.75 11.9779 15.75 8.25C15.75 4.52208 12.7279 1.5 9 1.5Z" stroke="currentColor" stroke-width="1.125" />
                                            <path d="M6.75 8.25H11.25" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            <path d="M9 6V10.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                        </svg>
                                    </div>
                                    <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('AI Tools') }}</p>
                                </a>
                            @endadmincan

                            @admincan('admin.accessibility_control.access')
                                <a href="{{ route('admin.accessibility-control.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.accessibility-control.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                    <div class="relative shrink-0 w-[18px] h-[18px]">
                                        <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                            <path d="M3.75 6.75H14.25" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            <path d="M3.75 11.25H14.25" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            <path d="M6.75 6.75V11.25" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            <path d="M11.25 6.75V11.25" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            <path d="M5.25 6.75V5.625C5.25 4.79657 5.92157 4.125 6.75 4.125H11.25C12.0784 4.125 12.75 4.79657 12.75 5.625V6.75" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                            <path d="M5.25 11.25V12.375C5.25 13.2034 5.92157 13.875 6.75 13.875H11.25C12.0784 13.875 12.75 13.2034 12.75 12.375V11.25" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                        </svg>
                                    </div>
                                    <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Accessibility Control') }}</p>
                                </a>
                            @endadmincan

                            @admincan('admin.api.access')
                                <a href="{{ route('admin.api.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.api.index') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                    <div class="relative shrink-0 w-[18px] h-[18px]">
                                        <svg class="block w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('API') }}</p>
                                </a>
                            @endadmincan

                            @admincan('admin.delivery_servers.access')
                                <a href="{{ route('admin.integrations.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.integrations.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                    <div class="relative shrink-0 w-[18px] h-[18px]">
                                        <svg class="block w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0l-4 4H8l-4-4m16 0H4" />
                                        </svg>
                                    </div>
                                    <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Integrations') }}</p>
                                </a>
                            @endadmincan
                        </div>
                    </div>

                    {{-- Marketing --}}
                    <div class="flex flex-col gap-3 items-start relative shrink-0 w-full">
                        <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px] ml-3">{{ __('Marketing') }}</p>

                        <div class="flex flex-col items-start relative shrink-0 w-full">
                            <a href="{{ route('admin.campaigns.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.campaigns.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M11.1947 2.18327L6.20514 4.57839C5.82113 4.76272 5.41082 4.8089 4.99256 4.7152C4.71883 4.65388 4.58194 4.62322 4.47172 4.61063C3.10307 4.45434 2.25 5.53756 2.25 6.7832V7.4668C2.25 8.71245 3.10307 9.79567 4.47172 9.63937C4.58194 9.62677 4.71884 9.5961 4.99256 9.53482C5.41082 9.44107 5.82113 9.48727 6.20514 9.67162L11.1947 12.0667C12.3401 12.6166 12.9127 12.8914 13.5513 12.6772C14.1898 12.4629 14.4089 12.0031 14.8473 11.0835C16.0509 8.5584 16.0509 5.69164 14.8473 3.16647C14.4089 2.24689 14.1898 1.78711 13.5513 1.57282C12.9127 1.35855 12.3401 1.63345 11.1947 2.18327Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M8.59358 15.5782L7.47506 16.5C4.95387 14.5004 5.26188 13.5469 5.26188 9.75H6.11225C6.45734 11.8957 7.27134 12.912 8.39453 13.6478C9.0864 14.1009 9.22905 15.0544 8.59358 15.5782Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M5.625 9.375V4.875" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Campaigns') }}</p>
                            </a>

                            <a href="{{ route('admin.lists.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.lists.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M1.5 14.25L6.68477 10.7179C8.57903 9.42737 9.42097 9.42737 11.3152 10.7179L16.5 14.25" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M1.51194 10.9132C1.56139 13.1882 1.58613 14.3257 2.43458 15.1681C3.28303 16.0105 4.46387 16.0396 6.82554 16.0981C8.27947 16.134 9.72052 16.134 11.1745 16.0981C13.5361 16.0396 14.7169 16.0105 15.5654 15.1681C16.4139 14.3257 16.4386 13.1882 16.4881 10.9132C16.5123 9.79867 16.4996 8.69505 16.45 7.56908C16.4193 6.86973 16.4039 6.52006 16.2265 6.20988C16.0492 5.89971 15.7435 5.69951 15.132 5.29912L12.3114 3.45214C10.7056 2.40072 9.90285 1.875 9 1.875C8.09715 1.875 7.29433 2.40071 5.68862 3.45214L2.86798 5.29912C2.25652 5.69951 1.95079 5.89971 1.77344 6.20988C1.59609 6.52006 1.5807 6.86974 1.54992 7.56908C1.50037 8.69505 1.48771 9.79867 1.51194 10.9132Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M16.5 7.125L13.301 9.4554C12.5253 10.0204 11.8878 10.5 10.875 10.5M1.5 7.125L4.69903 9.4554C5.47466 10.0204 6.11221 10.5 7.125 10.5" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Email Lists') }}</p>
                            </a>

                            @admincan('admin.public_templates.access')
                                <a href="{{ route('admin.public-templates.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.public-templates.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                    <div class="relative shrink-0 w-[18px] h-[18px]">
                                        <svg class="block w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Templates') }}</p>
                                </a>
                            @endadmincan

                            @admincan('admin.public_template_categories.access')
                                <a href="{{ route('admin.public-template-categories.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.public-template-categories.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                    <div class="relative shrink-0 w-[18px] h-[18px]">
                                        <svg class="block w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                        </svg>
                                    </div>
                                    <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Template Categories') }}</p>
                                </a>
                            @endadmincan

                            @admincan('admin.email_validation.access')
                                <a href="{{ route('admin.email-validation.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.email-validation.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                    <div class="relative shrink-0 w-[18px] h-[18px]">
                                        <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                            <path d="M2.25 4.5C2.25 3.67157 2.92157 3 3.75 3H14.25C15.0784 3 15.75 3.67157 15.75 4.5V13.5C15.75 14.3284 15.0784 15 14.25 15H3.75C2.92157 15 2.25 14.3284 2.25 13.5V4.5Z" stroke="currentColor" stroke-width="1.125" />
                                            <path d="M4.5 6H13.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            <path d="M4.5 9H10.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            <path d="M4.5 12H9" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                            <path d="M12.75 9.75L13.5 10.5L15 9" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        </svg>
                                    </div>
                                    <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Email Validation') }}</p>
                                </a>
                            @endadmincan

                            <a href="{{ route('admin.customers.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.customers.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M2.83321 8.9568C2.1222 7.71698 1.77889 6.70461 1.57188 5.67841C1.26571 4.16069 1.96636 2.67811 3.12703 1.7321C3.61759 1.33229 4.17992 1.46889 4.47 1.9893L5.12488 3.16418C5.64397 4.09543 5.9035 4.56104 5.85202 5.05469C5.80055 5.54834 5.45053 5.9504 4.75048 6.75451L2.83321 8.9568ZM2.83321 8.9568C4.27238 11.4662 6.53088 13.726 9.0432 15.1668M9.0432 15.1668C10.283 15.8778 11.2954 16.2212 12.3216 16.4282C13.8393 16.7343 15.3219 16.0337 16.2679 14.873C16.6677 14.3825 16.5311 13.8201 16.0107 13.53L14.8358 12.8751C13.9045 12.356 13.4389 12.0965 12.9453 12.148C12.4516 12.1994 12.0496 12.5495 11.2455 13.2495L9.0432 15.1668Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M9 5.25H9.64282C9.9459 5.25 10.0974 5.25 10.1916 5.34153C10.2857 5.43306 10.2857 5.58038 10.2857 5.875C10.2857 6.46426 10.2857 6.75888 10.0974 6.94194C9.95422 7.08116 9.74467 7.1145 9.38587 7.12249C9.2019 7.12658 9.10988 7.12863 9.0549 7.18327C9 7.23792 9 7.32528 9 7.5V8.37503C9 8.66963 9 8.81693 9.09412 8.9085C9.18832 9 9.33982 9 9.64282 9H10.2857M13.5 5.25V7.125M13.5 7.125H12.4072C12.1647 7.125 12.0435 7.125 11.9682 7.05178C11.8928 6.97856 11.8928 6.8607 11.8928 6.625V5.25M13.5 7.125V9" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M7.5 3.22889C7.56675 3.15389 7.63613 3.08038 7.70805 3.00847C9.71933 0.997178 12.9803 0.997178 14.9915 3.00847C17.0028 5.01975 17.0028 8.28067 14.9915 10.2919C14.9196 10.3639 14.8461 10.4332 14.7711 10.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Customers') }}</p>
                            </a>

                            <a href="{{ route('admin.customer-groups.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.customer-groups.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M9 5.625C9 7.07475 7.82475 8.25 6.375 8.25C4.92525 8.25 3.75 7.07475 3.75 5.625C3.75 4.17525 4.92525 3 6.375 3C7.82475 3 9 4.17525 9 5.625Z" stroke="currentColor" stroke-width="1.125" />
                                        <path d="M10.125 8.25C11.5747 8.25 12.75 7.07475 12.75 5.625C12.75 4.17525 11.5747 3" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                        <path d="M9.85718 15H2.89286C2.1236 15 1.5 14.4244 1.5 13.7143C1.5 11.9391 3.05901 10.5 4.98215 10.5H7.76783C8.55173 10.5 9.27518 10.7391 9.85718 11.1426" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M14.25 10.5V15M16.5 12.75H12" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Groups') }}</p>
                            </a>
                        </div>
                    </div>

                    {{-- Delivery --}}
                    <div class="flex flex-col gap-3 items-start relative shrink-0 w-full">
                        <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px] ml-3">{{ __('Delivery') }}</p>

                        <div class="flex flex-col items-start relative shrink-0 w-full">
                            <a href="{{ route('admin.delivery-servers.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.delivery-servers.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M3 10.5H4.79612C5.01673 10.5 5.23431 10.5497 5.43163 10.6452L6.96311 11.3862C7.16043 11.4817 7.37801 11.5313 7.59863 11.5313H8.38057C9.13687 11.5313 9.75 12.1247 9.75 12.8565C9.75 12.8861 9.72975 12.9121 9.70035 12.9202L7.79468 13.4471C7.4528 13.5416 7.08675 13.5087 6.76875 13.3548L5.13158 12.5627" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M9.75 12.375L13.1946 11.3167C13.8053 11.1264 14.4653 11.352 14.8478 11.8817C15.1244 12.2647 15.0118 12.8132 14.6089 13.0457L8.97218 16.2979C8.61368 16.5047 8.19067 16.5552 7.7964 16.4382L3 15.0149" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M11.25 9H9.75C8.3358 9 7.6287 9 7.18934 8.56065C6.75 8.1213 6.75 7.41421 6.75 6V4.5C6.75 3.08579 6.75 2.37868 7.18934 1.93934C7.6287 1.5 8.3358 1.5 9.75 1.5H11.25C12.6642 1.5 13.3713 1.5 13.8106 1.93934C14.25 2.37868 14.25 3.08579 14.25 4.5V6C14.25 7.41421 14.25 8.1213 13.8106 8.56065C13.3713 9 12.6642 9 11.25 9Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M9.75 3.75H11.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Delivery Servers') }}</p>
                            </a>

                            <a href="{{ route('admin.sending-domains.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.sending-domains.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M9 16.125V5.25M11.25 14.25C10.8076 14.7051 9.63023 16.5 9 16.5C8.36977 16.5 7.19238 14.7051 6.75 14.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M15.1745 8.625C16.0582 9.0465 16.5 9.33038 16.5 9.75008C16.5 10.2701 15.8219 10.5815 14.4655 11.2046L11.9176 12.375M2.82545 8.625C1.94182 9.0465 1.5 9.33038 1.5 9.75008C1.5 10.2701 2.17817 10.5815 3.53452 11.2046L6.08243 12.375" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M6.08259 7.875L3.53452 6.70452C2.17817 6.08147 1.5 5.76995 1.5 5.25C1.5 4.73005 2.17817 4.41853 3.53452 3.79548L7.2043 2.10973C8.0892 1.70324 8.53163 1.5 9 1.5C9.46837 1.5 9.9108 1.70324 10.7957 2.10973L14.4655 3.79548C15.8219 4.41853 16.5 4.73005 16.5 5.25C16.5 5.76995 15.8219 6.08147 14.4655 6.70453L11.9174 7.875" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Sending Domains') }}</p>
                            </a>

                            <a href="{{ route('admin.tracking-domains.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.tracking-domains.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M8.99997 1.5C5.27205 1.5 2.24997 4.52208 2.24997 8.25C2.24997 11.9779 5.27205 15 8.99997 15C12.7279 15 15.75 11.9779 15.75 8.25C15.75 4.52208 12.7279 1.5 8.99997 1.5Z" stroke="currentColor" stroke-width="1.125" />
                                        <path d="M9 5.25V8.25L11.25 9.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Tracking Domains') }}</p>
                            </a>

                            <a href="{{ route('admin.bounce-servers.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.bounce-servers.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M1.5 7.5C1.5 5.37868 1.5 4.31802 2.15901 3.65901C2.81802 3 3.87868 3 6 3H12C14.1213 3 15.1819 3 15.841 3.65901C16.5 4.31802 16.5 5.37868 16.5 7.5V10.5C16.5 12.6213 16.5 13.6819 15.841 14.341C15.1819 15 14.1213 15 12 15H6C3.87868 15 2.81802 15 2.15901 14.341C1.5 13.6819 1.5 12.6213 1.5 10.5V7.5Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M4.5 7.42822C4.5 4.01991 9 7.49261 9 9.75H6.375C5.0724 9.75 4.5 8.60242 4.5 7.42822Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M13.5 7.42822C13.5 4.01991 9 7.49261 9 9.75H11.625C12.9276 9.75 13.5 8.60242 13.5 7.42822Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M9 3V15" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M1.5 9.75H16.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M11.25 12L9 9.75L6.75 12" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Bounce Servers') }}</p>
                            </a>

                            <a href="{{ route('admin.reply-servers.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.reply-servers.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M1.5 7.5C1.5 5.37868 1.5 4.31802 2.15901 3.65901C2.81802 3 3.87868 3 6 3H12C14.1213 3 15.1819 3 15.841 3.65901C16.5 4.31802 16.5 5.37868 16.5 7.5V10.5C16.5 12.6213 16.5 13.6819 15.841 14.341C15.1819 15 14.1213 15 12 15H6C3.87868 15 2.81802 15 2.15901 14.341C1.5 13.6819 1.5 12.6213 1.5 10.5V7.5Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M4.5 7.42822C4.5 4.01991 9 7.49261 9 9.75H6.375C5.0724 9.75 4.5 8.60242 4.5 7.42822Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M13.5 7.42822C13.5 4.01991 9 7.49261 9 9.75H11.625C12.9276 9.75 13.5 8.60242 13.5 7.42822Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M9 3V15" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M1.5 9.75H16.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M11.25 12L9 9.75L6.75 12" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Reply Servers') }}</p>
                            </a>

                            <a href="{{ route('admin.bounced-emails.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.bounced-emails.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M1.5 4.125L6.68477 7.06847C8.57903 8.14385 9.42097 8.14385 11.3152 7.06847L16.5 4.125" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M8.625 14.9969C8.27527 14.9923 7.17512 15.0058 6.82412 14.9969C4.46275 14.9374 3.28206 14.9077 2.43372 14.0545C1.58537 13.2013 1.56086 12.0496 1.51183 9.74602C1.49606 9.00532 1.49605 8.26905 1.51182 7.52835C1.56085 5.22481 1.58537 4.07304 2.43371 3.21984C3.28206 2.36663 4.46275 2.33691 6.82411 2.27747C8.27947 2.24084 9.72053 2.24084 11.1759 2.27748C13.5373 2.33692 14.7179 2.36665 15.5662 3.21985C16.4146 4.07305 16.4392 5.22482 16.4881 7.52835C16.4987 8.02297 16.5022 7.76565 16.4986 8.259" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M11.2613 10.79C12.0657 10.3102 12.7679 10.5035 13.1897 10.8115C13.3627 10.9378 13.4492 11.0009 13.5 11.0009C13.5509 11.0009 13.6373 10.9378 13.8103 10.8115C14.2322 10.5035 14.9343 10.3102 15.7388 10.79C16.7946 11.4196 17.0335 13.4969 14.5982 15.2493C14.1343 15.5831 13.9024 15.75 13.5 15.75C13.0976 15.75 12.8657 15.5831 12.4019 15.2493C9.96653 13.4969 10.2054 11.4196 11.2613 10.79Z" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Bounce Email') }}</p>
                            </a>
                        </div>
                    </div>

                    {{-- Monetization --}}
                    <div class="flex flex-col gap-3 items-start relative shrink-0 w-full">
                        <p class="font-normal leading-[18px] relative shrink-0 text-[#a8a8a8] dark:text-admin-text-secondary text-xs tracking-[-0.36px] ml-3">{{ __('Monetization') }}</p>

                        <div class="flex flex-col items-start relative shrink-0 w-full">
                            <a href="{{ route('admin.invoices.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.invoices.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M9.66068 5.26242L13.2403 6.21619M8.89335 8.11005L10.6831 8.5869M8.98237 13.4748L9.69832 13.6656C11.7232 14.2051 12.7358 14.4749 13.5334 14.017C14.331 13.559 14.6023 12.5522 15.1449 10.5387L15.9122 7.6911C16.4548 5.67754 16.7261 4.67076 16.2656 3.87762C15.8051 3.08448 14.7926 2.81471 12.7676 2.27518L12.0517 2.08443C10.0267 1.54489 9.01425 1.27513 8.21662 1.73305C7.41897 2.19097 7.14767 3.19775 6.60508 5.21131L5.83774 8.0589C5.29515 10.0724 5.02386 11.0792 5.48437 11.8723C5.94489 12.6655 6.95738 12.9353 8.98237 13.4748Z" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                        <path d="M9 15.7096L8.28578 15.9041C6.26552 16.4542 5.25542 16.7293 4.45964 16.2624C3.66388 15.7955 3.39322 14.769 2.8519 12.7159L2.08637 9.81246C1.54505 7.75941 1.27439 6.73287 1.73383 5.92418C2.13125 5.22463 3 5.2501 4.125 5.25001" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Invoices') }}</p>
                            </a>

                            <a href="{{ route('admin.manual-payments.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.manual-payments.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M1.5 4.125L6.68477 7.06847C8.57903 8.14385 9.42097 8.14385 11.3152 7.06847L16.5 4.125" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M2.25 6.75V12.375C2.25 13.8247 2.25 14.5496 2.7119 15.0248C3.1738 15.5 3.91746 15.5 5.40479 15.5H12.5952C14.0825 15.5 14.8262 15.5 15.2881 15.0248C15.75 14.5496 15.75 13.8247 15.75 12.375V6.75" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                        <path d="M6 10.125H12" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Manual Payments') }}</p>
                            </a>

                            <a href="{{ route('admin.coupons.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.coupons.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M1.84829 7.00781C1.66188 7.00781 1.49178 6.85672 1.50036 6.65921C1.55054 5.50265 1.69115 4.74973 2.0851 4.15413C2.31175 3.81147 2.59328 3.51344 2.91696 3.27351C3.79185 2.625 5.02608 2.625 7.49454 2.625H10.5055C12.974 2.625 14.2082 2.625 15.0832 3.27351C15.4068 3.51344 15.6883 3.81147 15.915 4.15413C16.3089 4.74967 16.4495 5.50249 16.4997 6.65882C16.5083 6.85656 16.338 7.00781 16.1514 7.00781C15.112 7.00781 14.2695 7.89975 14.2695 9C14.2695 10.1003 15.112 10.9922 16.1514 10.9922C16.338 10.9922 16.5083 11.1434 16.4997 11.3412C16.4495 12.4976 16.3089 13.2503 15.915 13.8459C15.6883 14.1885 15.4068 14.4865 15.0832 14.7265C14.2082 15.375 12.974 15.375 10.5055 15.375H7.49454C5.02608 15.375 3.79185 15.375 2.91696 14.7265C2.59328 14.4865 2.31175 14.1885 2.0851 13.8459C1.69115 13.2503 1.55054 12.4973 1.50036 11.3408C1.49178 11.1433 1.66188 10.9922 1.84829 10.9922C2.88763 10.9922 3.73018 10.1003 3.73018 9C3.73018 7.89975 2.88763 7.00781 1.84829 7.00781Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M7.12505 10.875L10.8751 7.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M7.12505 7.125H7.13347M10.8666 10.875H10.8751" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Coupons') }}</p>
                            </a>

                            <a href="{{ route('admin.plans.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.plans.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M9.66068 5.26242L13.2403 6.21619M8.89335 8.11005L10.6831 8.5869M8.98237 13.4748L9.69832 13.6656C11.7232 14.2051 12.7358 14.4749 13.5334 14.017C14.331 13.559 14.6023 12.5522 15.1449 10.5387L15.9122 7.6911C16.4548 5.67754 16.7261 4.67076 16.2656 3.87762C15.8051 3.08448 14.7926 2.81471 12.7676 2.27518L12.0517 2.08443C10.0267 1.54489 9.01425 1.27513 8.21662 1.73305C7.41897 2.19097 7.14767 3.19775 6.60508 5.21131L5.83774 8.0589C5.29515 10.0724 5.02386 11.0792 5.48437 11.8723C5.94489 12.6655 6.95738 12.9353 8.98237 13.4748Z" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                        <path d="M9 15.7096L8.28578 15.9041C6.26552 16.4542 5.25542 16.7293 4.45964 16.2624C3.66388 15.7955 3.39322 14.769 2.8519 12.7159L2.08637 9.81246C1.54505 7.75941 1.27439 6.73287 1.73383 5.92418C2.13125 5.22463 3 5.2501 4.125 5.25001" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Plans') }}</p>
                            </a>

                            <a href="{{ route('admin.payment-methods.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.payment-methods.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M12.3305 16.0108L12.7447 15.4932C13.1086 15.0383 13.8814 15.075 14.1888 15.5616C14.5586 16.1471 15.4719 16.0307 15.7252 15.4922C15.7384 15.4642 15.7054 15.5342 15.7336 15.2919C15.7617 15.0496 15.75 14.9942 15.7265 14.8836L14.2842 8.07308C13.9217 6.36146 13.7405 5.50565 13.117 5.00282C12.4936 4.5 11.6136 4.5 9.8538 4.5H8.1462C6.38638 4.5 5.50646 4.5 4.883 5.00282C4.25953 5.50565 4.07828 6.36146 3.71579 8.07308L2.27348 14.8836C2.25003 14.9942 2.23831 15.0496 2.26646 15.2919C2.2946 15.5342 2.26166 15.4642 2.27483 15.4922C2.52813 16.0307 3.44142 16.1471 3.81124 15.5616C4.11864 15.075 4.8914 15.0383 5.25532 15.4932L5.66951 16.0108C6.19136 16.663 7.28114 16.663 7.803 16.0108L7.8681 15.9294C8.42183 15.2374 9.57818 15.2374 10.1319 15.9294L10.197 16.0108C10.7189 16.663 11.8087 16.663 12.3305 16.0108Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M1.86252 7.125C1.42354 6.66899 1.50618 6.0817 1.50618 4.61382C1.50618 3.14595 1.50618 2.41202 1.94517 1.95601C2.38415 1.5 3.09068 1.5 4.50373 1.5H13.4964C14.9094 1.5 15.616 1.5 16.055 1.95601C16.4939 2.41202 16.4939 3.14595 16.4939 4.61382C16.4939 6.0817 16.5761 6.66899 16.1371 7.125" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                        <path d="M9 7.5H6.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M10.5 10.5H6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Payment Method') }}</p>
                            </a>

                            <a href="{{ route('admin.vat-tax.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.vat-tax.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M1.84829 7.00781C1.66188 7.00781 1.49178 6.85672 1.50036 6.65921C1.55054 5.50265 1.69115 4.74973 2.0851 4.15413C2.31175 3.81147 2.59328 3.51344 2.91696 3.27351C3.79185 2.625 5.02608 2.625 7.49454 2.625H10.5055C12.974 2.625 14.2082 2.625 15.0832 3.27351C15.4068 3.51344 15.6883 3.81147 15.915 4.15413C16.3089 4.74967 16.4495 5.50249 16.4997 6.65882C16.5083 6.85656 16.338 7.00781 16.1514 7.00781C15.112 7.00781 14.2695 7.89975 14.2695 9C14.2695 10.1003 15.112 10.9922 16.1514 10.9922C16.338 10.9922 16.5083 11.1434 16.4997 11.3412C16.4495 12.4976 16.3089 13.2503 15.915 13.8459C15.6883 14.1885 15.4068 14.4865 15.0832 14.7265C14.2082 15.375 12.974 15.375 10.5055 15.375H7.49454C5.02608 15.375 3.79185 15.375 2.91696 14.7265C2.59328 14.4865 2.31175 14.1885 2.0851 13.8459C1.69115 13.2503 1.55054 12.4973 1.50036 11.3408C1.49178 11.1433 1.66188 10.9922 1.84829 10.9922C2.88763 10.9922 3.73018 10.1003 3.73018 9C3.73018 7.89975 2.88763 7.00781 1.84829 7.00781Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M7.12505 10.875L10.8751 7.125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M7.12505 7.125H7.13347M10.8666 10.875H10.8751" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Vat/Tax') }}</p>
                            </a>

                            <a href="{{ route('admin.affiliates.index') }}" class="flex gap-2.5 items-center relative w-full shrink-0 rounded-lg px-3 py-2 {{ request()->routeIs('admin.affiliates.*') ? 'text-[#1E5FEA] bg-[rgba(30,95,234,0.08)] dark:bg-[rgba(30,95,234,0.12)]' : 'text-[#1b1b20] dark:text-admin-text-primary' }} hover:bg-gray-50 dark:hover:bg-white/5 hover:text-[#1E5FEA] dark:hover:text-[#1E5FEA] transition-colors">
                                <div class="relative shrink-0 w-[18px] h-[18px]">
                                    <svg class="block w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18 18">
                                        <path d="M3.75 15.75V3.75C3.75 2.92157 4.42157 2.25 5.25 2.25H12.75C13.5784 2.25 14.25 2.92157 14.25 3.75V15.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.125" />
                                        <path d="M6 6.75H12" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                        <path d="M6 9.75H12" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                        <path d="M6 12.75H10.5" stroke="currentColor" stroke-linecap="round" stroke-width="1.125" />
                                    </svg>
                                </div>
                                <p class="font-normal leading-[22px] relative shrink-0 text-sm tracking-[-0.48px]">{{ __('Affiliation') }}</p>
                            </a>
                        </div>
                    </div>

                </nav>

         </div>
     </div>
 </aside>
