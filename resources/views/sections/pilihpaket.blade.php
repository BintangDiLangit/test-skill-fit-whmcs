<div class="p-6 flex flex-col gap-4">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="w-full">
            <div class="flex flex-col mb-2">
                <h6 class="font-bold">Pilih Paket</h6>
                <p class="text-xs -mt-1">Sesuaikan dengan kebutuhan</p>
            </div>
            <div x-data="{ show: false }" class="dropdown">
                <button @click="show = !show"
                    class="w-full border border-default flex items-center justify-between rounded py-3 px-4 min-h-[5rem]">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <h6 class="text-left font-bold">S2</h6>
                            <div class="bg-blue-100 text-blue-500 rounded text-xs py-1 px-2 text-center font-medium">
                                Gratis Domain .MY.ID / .BIZ.ID / .WEB.ID</div>
                        </div>
                        <p class="text-sm font-medium mt-1 flex items-center gap-0.5 flex-wrap text-left">
                            ± 1 Jt Visitor/bulan
                            | <span class="text-orange-500 font-extrabold">FREE 5</span>
                            premium WP Plugin <span class="custom-tooltip text-orange-500"
                                data-tooltip="LiteSpeed Cache, Seo Premium, Whatsapp Support, Wordfence, Smush Pro"><i
                                    class="fa fa-info-circle ml-0.5"></i></span>
                        </p>
                    </div>
                    <div class="text-xs">
                        &#9660;
                    </div>
                </button>
            </div>
        </div>
        <div class="w-full">
            <div class="flex flex-col mb-2">
                <h6 class="font-bold">Pilih Billing Cycle</h6>
                <p class="text-xs -mt-1">Harga perpanjangan tetap sama</p>
            </div>
            <div>
                <div x-data="billingCycle" class="dropdown">
                    <button @click="show = !show" @click.outside="show = false"
                        class="w-full border border-default flex items-center justify-between rounded py-3 px-4 h-20">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <h6 class="text-left font-bold capitalize" x-bind="selectedBillingCycle.name"></h6>

                                <div class="bg-red-500 text-white rounded text-xs py-1 px-2 text-center font-medium"
                                    x-bind="selectedBillingCycle.discount" x-cloak></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium mt-1" x-bind="selectedBillingCycle.price"></p>
                                <p class="text-sm font-medium mt-1 line-through text-red-500"
                                    x-bind="selectedBillingCycle.gimmickPrice" x-cloak></p>
                            </div>
                        </div>
                        <div class="text-xs" x-show="!isLoading" x-cloak>
                            &#9660;
                        </div>
                        <div class="w-full flex items-center justify-center" x-show="isLoading">
                            <div class="loader border-black"></div>
                        </div>
                    </button>
                    <div @click="show = false;" @click.outside="show = false;" class="dropdown-items transform-gpu"
                        x-show="show" x-transition>
                        <template x-for="(billingCycle, i) in billingCycles">
                            <div class="w-full border border-default flex items-center justify-between rounded py-3 px-4"
                                @click="setActiveBillingCycle(i)">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <h6 class="text-left font-bold capitalize"
                                            x-text="Order.renderBillingCycleToHumanReadable(billingCycle.name)">
                                        </h6>
                                        <div class="bg-red-500 text-white rounded text-xs py-1 px-2 text-center font-medium"
                                            x-show="billingCycle.discount > 0"><span
                                                x-text="billingCycle.discount"></span>% OFF
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium mt-1"
                                            x-text="Order.renderIDRValue(billingCycle.price)">
                                        </p>
                                        <p class="text-sm font-medium mt-1 line-through text-red-500"
                                            x-show="billingCycle.discount > 0"
                                            x-text="Order.renderIDRValue(billingCycle.gimmickPrice)">
                                        </p>
                                    </div>
                                </div>
                                </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="flex items-center p-6 bg-gray-faded rounded gap-5 w-full">
        <div class="flex-none flex items-center justify-center w-fit text-4xl">
            <i class="fa fa-info-circle text-orange-500"></i>
        </div>
        <div class="flex flex-col justify-center gap-0.5 grow">
            <p>
                <span class="font-extrabold">Gratis Domain</span>
                dengan pilihan billing cycle minimal 1 Tahun
            </p>
            <p class="text-sm">.MY.ID / .BIZ.ID / .WEB.ID</p>
        </div>
    </div>
</div>
