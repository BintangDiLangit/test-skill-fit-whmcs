<div class="p-6 flex flex-col gap-4">
    <h6 class="font-bold">Masukkan Domain untuk Layananmu</h6>
    <div class="flex gap-6">
        <div class="w-full">
            <div class="flex flex-col gap-4">
                <label class="w-full flex items-start gap-y-2 gap-x-4 text-sm cursor-pointer font-bold">
                    <input type="radio" x-model="domainType" value="Register" class="text-primary"
                        @change="addToCart(false, true)" checked>
                    <span class="flex flex-col">
                        Daftarkan Domain Baru
                    </span>
                </label>
                <label class="w-full flex items-start gap-y-2 gap-x-4 text-sm cursor-pointer font-bold">
                    <input type="radio" x-model="domainType" value="Transfer" class="text-primary"
                        @change="addToCart(false, true)">
                    <span class="flex flex-col">
                        Transfer dari provider lain ke Jagoan Hosting
                    </span>
                </label>
                <label class="w-full flex items-start gap-y-2 gap-x-4 text-sm cursor-pointer font-bold">
                    <input type="radio" x-model="domainType" value="Use" class="text-primary"
                        @change="addToCart(false, true)">
                    <span class="flex flex-col">
                        Saya sudah punya domain dan ingin menggunakannya
                        <small class="font-normal text-xs">
                            Dengan mengarahkan nameserver domain ke server layanan Jagoan
                            Hosting
                        </small>
                    </span>
                </label>
            </div>
        </div>
    </div>
    <div class="w-full flex flex-col md:flex-row gap-4">
        <div class="flex items-start gap-4 w-full">
            <div class="w-full">
                <input type="text" placeholder="Search domain"
                    class="flex-1 border border-default rounded text-sm py-3 px-4 w-full peer invalid:border-red-500"
                    required x-model="domainName" @input.debounce.2000ms="addToCart">
            </div>
            <div class="border border-default rounded font-bold text-sm w-40 min-w-[100px] relative">
                <button class="w-full py-3 px-4 flex justify-between items-center gap-1"
                    @click="!showAvailableExtensions && toggleAvailableExtensions">
                    <span x-show="!isLoading" x-text="activeExtension.name" x-cloak></span>
                    <span class="text-xs" x-show="!isLoading" x-cloak>
                        <i class="fa fa-chevron-down"></i>
                    </span>
                    <div class="w-full flex items-center justify-center" x-show="isLoading">
                        <span class="loader border-primary"></span>
                    </div>
                </button>
                <div class="absolute bg-white shadow-lg rounded w-64 h-fit max-h-44 mt-4 flex flex-col right-0 z-10"
                    @click.outside="toggleAvailableExtensions" x-show="showAvailableExtensions" x-transition x-cloak>
                    <div class="py-1 px-3 flex-none flex gap-1 items-center">
                        <input type="text" class="w-full border-none text-sm px-1" placeholder="cari domain..."
                            x-model="searchKeyword" x-ref="domainSearchInput">
                        <i class="fa fa-search text-sm flex-none"></i>
                    </div>
                    <div class="flex-1 h-full w-full flex flex-col overflow-y-scroll border-t border-slate-200">
                        <template
                            x-for="extension, index in availableExtensions.filter(i => i.name.includes(searchKeyword.toLowerCase()))">
                            <div class="px-4 py-1 w-full cursor-pointer hover:pl-6 hover:bg-gray-faded duration-300 font-normal flex justify-between"
                                @click="setActiveExtension(index)">
                                <div class="flex items-center gap-1">
                                    <span x-text="extension.name"></span>
                                    <span class="rounded text-xs uppercase px-2 py-0.5"
                                        :class="true && getDomainGroupColor(extension.conf.group)"
                                        x-text="extension.conf.group + '!'" x-show="extension.conf.group !== ''"></span>
                                </div>
                                <span
                                    x-text="domainType !== 'Use' ? Order.renderIDRValue(extension.conf[domainType.toLowerCase()][1]) : '-'"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <button
            class="min-w-[100px] py-3 px-4 h-fit hidden md:flex items-center justify-center flex-none duration-200 text-sm rounded w-full md:w-fit bg-gray text-primary"
            :class="domainName === '' || isLoading || isAdding ? '' : '!bg-secondary !text-white'" @click="addToCart">
            <span x-show="!isLoading && !isAdding" x-cloak>Cek</span>
            <span class="loader text-white" x-show="isLoading || isAdding"></span>
        </button>
    </div>
    <div class="w-full" x-show="domainType === 'Transfer'" x-cloak>
        <input type="text" placeholder="Kode EPP"
            class="flex-1 border border-default rounded text-sm py-3 px-4 w-full peer invalid:border-red-500" required
            x-model="eppCode" @input.debounce.2000ms="addToCart">
    </div>
    <button
        class="min-w-[100px] py-3 px-4 h-fit flex md:hidden items-center justify-center flex-none duration-200 text-sm rounded w-full bg-gray text-primary"
        :class="domainName === '' || isLoading || isAdding ? '' : '!bg-secondary !text-white'" @click="addToCart">
        <span x-show="!isLoading && !isAdding" x-cloak>Cek</span>
        <span class="loader text-white" x-show="isLoading || isAdding"></span>
    </button>

    <div class="bg-gray-faded text-primary rounded p-4 flex items-center gap-2 text-sm" x-show="isAdding" x-cloak
        x-transition.in>
        <span class="loader"></span>
        <span>Sedang mengecek domain kamu, Sob!</span>
    </div>
    <div class="bg-red-500 text-white rounded p-4 flex items-center gap-2 text-sm"
        x-show="domainType === 'Transfer' && eppCode === ''" x-cloak x-transition.in>
        <i class="fa fa-warning"></i>
        <span>Kode EPP belum ditambahkan, Sob!</span>
    </div>
    <div class="bg-green-500 text-white rounded p-4 flex items-center gap-2 text-sm"
        x-show="isTransferable && eppCode !== '' && domainType === 'Transfer' && !isAdding" x-cloak x-transition.in>
        <i class="fa fa-check-circle"></i>
        <span>Domain memenuhi syarat untuk melakukan transfer domain</span>
    </div>
    <div class="bg-red-500 text-white rounded p-4 flex items-center gap-2 text-sm"
        x-show="!isTransferable && domainName !== '' && domainType === 'Transfer' && eppCode !== '' && !isAdding"
        x-cloak x-transition.in>
        <i class="fa fa-times-circle"></i>
        <span>Domain tidak memenuhi syarat untuk melakukan transfer domain</span>
    </div>
    <div class="bg-red-500 text-white rounded p-4 flex items-center gap-2 text-sm"
        x-show="domainName !== '' && !isAvailable && !isLoading && !isAdding && domainType !== 'Use'" x-cloak
        x-transition.in>
        <i class="fa fa-warning"></i>
        <span>Domain tidak tersedia, Sob!</span>
    </div>
    <div class="bg-red-500 text-white rounded p-4 flex items-center gap-2 text-sm" x-show="!isDomainExistInCart"
        x-transition.in>
        <i class="fa fa-warning"></i>
        <span>Domain belum ditambahkan ke keranjang, Sob!</span>
    </div>
    <div class="bg-green-500 text-white rounded p-4 flex items-center gap-2 text-sm" x-show="isDomainExistInCart"
        x-cloak x-transition.in>
        <i class="fa fa-check-circle"></i>
        <span>Mantap! Domain sudah ditambahkan ke keranjang, Sob!</span>
    </div>
    <div class="bg-blue-100 p-6 text-sm rounded text-primary flex flex-col gap-y-4" x-show="domainType === 'Transfer'"
        x-cloak x-transition>
        <h3 class="text-lg">Persyaratan Transfer Domain</h3>
        <div>
            <h4 class="font-bold mb-1">Pastikan kamu memenuhi persyaratan berikut sebelum
                transfer domain di Jagoan Hosting</h4>
            <ul class="list-disc pl-8 space-y-1 mb-2">
                <li>Umur domain minimal 60 hari sebelum ditransfer</li>
                <li>Batas maksimal umur domain 30 hari sebelum expired</li>
                <li>Mempunyai EPP Code Domain dari provider sebelumnya</li>
                <li>
                    <div class="flex items-center">Domain dalam keadaan <span
                            class="custom-tooltip ml-1 items-center text-blue-500 cursor-help group"
                            data-tooltip="Registrar Lock adalah fitur untuk mengunci domain kamu agar tidak bisa ditransfer ke Registrar lain. Apabila kamu ingin melakukan transfer domain ke registrar lain, pastikan status domain dalam keadaan UNLOCK."><span
                                class="group-hover:underline">UNLOCK</span><i
                                class="fas fa-question-circle ml-1 text-xs"></i></span>
                    </div>
                </li>
            </ul>
            <p>Untuk beberapa Domain Indonesia memerlukan dokumen persyaratan tambahan.
                <a class="text-blue-500 group"
                    href="https://www.jagoanhosting.com/tutorial/tutorial-domain/aktivasi-domain-id?_gl=1*1eodwng*_gcl_aw*R0NMLjE2NjY4NzI2NzQuQ2p3S0NBancyT2lhQmhCU0Vpd0FoMlpTUHlMbzE2VWl1WmVNaF9HcEU3b2tYdDJjdUlLaGpFSmRjcjFtZUNfc3RUTEdOdEFJbUNrRWV4b0NKc01RQXZEX0J3RQ.."
                    target="_blank" rel="noopener noreferrer">
                    <span class="group-hover:underline">Cek persyaratan domain
                        Indonesia</span>
                    <i class="fas fa-external-link text-xs"></i>
                </a>
            </p>
        </div>
    </div>
    <div class="bg-blue-100 p-6 text-sm rounded text-primary flex flex-col gap-y-4"
        x-show="Object.keys(domainTerms).includes(activeExtension.name)" x-cloak x-transition>
        <h3 class="text-lg">Persyaratan Domain <span class="uppercase" x-text="activeExtension.name"></span></h3>
        <template x-for="term, index in domainTerms[activeExtension.name]">
            <div>
                <h4 class="font-bold mb-1" x-text="(index+1) + '. ' + term.subtitle"></h4>
                <ul class="list-disc pl-8 space-y-1">
                    <template x-for="tac in term.terms_and_conditions">
                        <li x-html="transformText(tac)"></li>
                    </template>
                </ul>
            </div>
        </template>
    </div>
</div>
