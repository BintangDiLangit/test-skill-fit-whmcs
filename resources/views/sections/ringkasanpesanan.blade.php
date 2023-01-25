<div class=" p-6 w-full lg:w-[340px] bg-primary flex flex-col gap-y-6 flex-none rounded text-default h-fit shadow">
    <h4 class="font-normal -mb-6 text-2xl">Ringkasan Pesanan</h4>
    <div>
        <div class="text-sm text-gray border-b border-gray-solid">
            <div class="my-6 flex items-center justify-between">
                <p>Detail</p>
                <small class="hover:underline no-underline cursor-pointer underline-offset-2 decoration-1"><span
                        x-text="$store.cart.items.length"></span> Items</small>
            </div>
            <div class="flex flex-col gap-2 mb-4">
                <template x-for="item in items.filter(i => i.type !== 'Promo')">
                    <div class="flex flex-col">
                        <div class="flex justify-between items-center gap-2">
                            <p x-text="item?.title ?? item.name"></p>
                            <p x-text="Order.renderIDRValue(item.gimmickPrice)"
                                x-show="item.totalDiscountValue > 0 && item.billingCycleType !== 'use'"
                                class="flex-none text-orange-500 line-through"></p>
                        </div>
                        <div class="flex justify-between items-center gap-2">
                            <p x-text="item.description" class="font-thin italic"></p>
                            <p x-text="item.billingCycleType !== 'use' ? Order.renderIDRValue(item.price) : '-'"
                                class="flex-none"></p>
                        </div>
                    </div>
                </template>
                <div class="flex flex-col gap-2 animate-pulse" x-bind="skeletonLoading"></div>
            </div>
        </div>
        <div class="text-sm text-gray border-b border-gray-solid"
            x-show="($store.cartAddons?.getItems?.length ?? 0) > 0 && isReady" x-cloak>
            <div class="my-6 flex items-center justify-between cursor-pointer group" @click="showAddons = !showAddons">
                <p>Addons</p>
                <small class="group-hover:underline no-underline underline-offset-2 decoration-1"><i
                        class="fa fa-chevron-down mr-2"></i><span
                        x-text="$store.cartAddons?.getItems?.length ?? 0"></span> Items</small>
            </div>
            <div class="flex flex-col gap-2 mb-4" x-show="showAddons" x-transition>
                <template x-for="item in $store.cartAddons.getItems">
                    <div class="flex flex-col">
                        <div class="flex justify-between items-center gap-2">
                            <p x-text="item?.title ?? item.name"></p>
                            <p x-text="Order.renderIDRValue(item.gimmickPrice)" x-show="item.totalDiscountValue > 0"
                                class="flex-none text-orange-500 line-through"></p>
                        </div>
                        <div class="flex justify-between items-center gap-2">
                            <p x-text="item.description" class="font-thin italic"></p>
                            <p x-text="Order.renderIDRValue(item.price)" class="flex-none">
                            </p>
                        </div>
                    </div>
                </template>
                <div class="flex flex-col gap-2 animate-pulse" x-bind="skeletonLoading"></div>
            </div>
        </div>
        <div class="text-sm text-gray border-gray-solid border-b">
            <div class="my-6 flex justify-between items-center gap-2" x-show="isReady" x-cloak>
                <p>Subtotal</p>
                <p class="flex-none" x-text="subTotal.renderedValue"></p>
            </div>
            <div class="my-6 flex flex-col gap-2 animate-pulse" x-bind="skeletonLoading">
            </div>
        </div>
        <div class="text-sm text-gray pt-6">
            <div class="flex justify-between items-center gap-2 mb-6" x-show="isReady" x-cloak>
                <p>PPN @ 11.00%</p>
                <p class="flex-none" x-text="ppn.renderedValue"></p>
            </div>
            <div class="my-6 flex flex-col gap-2 animate-pulse" x-bind="skeletonLoading">
            </div>
            <div class="mb-6 flex flex-col gap-2" x-show="isReady" x-cloak>
                <div class="w-full rounded bg-white/30 p-4 text-white flex items-center gap-2 cursor-pointer select-none"
                    @click="toggleDiscountItems">
                    <i class="fa fa-info-circle"></i>
                    <p class="flex-1" x-bind="discountItemsInfo"></p>
                    <div class="text-xs flex-none" x-show="$store.discountItems.length > 0" x-cloak>
                        &#9660;
                    </div>
                </div>
                <div x-show="showDiscountItems" x-transition x-cloak>
                    <template x-for="item in $store.discountItems.filter(i => i.type !== 'rawpromo')">
                        <div class="mt-1 flex justify-between items-center gap-2 text-orange-500">
                            <p x-text="item.name"></p>
                            <p class="flex-none" x-text="'- ' + Order.renderIDRValue(item.value, false)"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
    <div class="my-6 flex flex-col gap-2 animate-pulse" x-bind="skeletonLoading"></div>
    <div class="-mt-6" x-show="isReady" x-cloak>
        <sup class="line-through text-lg" x-text="finalPrice.renderedGimmickValue"
            x-show="finalPrice.showRenderedGimmickValue"></sup>
        <h2 class="text-4xl font-light" x-text="finalPrice.renderedValue"></h2>
        <div class="mt-6">
            <button
                class="p-4 h-fit text-center flex-none duration-200 text-sm bg-secondary text-white rounded w-full font-bold flex items-center justify-center gap-2"
                :class="{ 'bg-gray-solid cursor-progress': isLoading }" x-bind:disabled="isLoading" @click="checkout">
                <span x-show="!isLoading">Bayar Sekarang</span>
                <span class="font-normal" x-show="isLoading" x-cloak>Memproses
                    Pesananmu...</span>
                <span x-show="isLoading" class="loader" x-cloak></span>
            </button>
        </div>
    </div>
</div>
<div>
    <label class="w-full flex items-center gap-y-2 gap-x-4 text-sm cursor-pointer font-bold">
        <input type="checkbox" class="text-primary rounded w-4 h-4" name="agree_tos" x-model="tos" checked>
        <span class="font-normal text-primary select-none">
            Saya telah membaca dan menyetujui <a href="https://www.jagoanhosting.com/aturan-layanan/#tos"
                target="_blank" class="font-bold hover:underline">Aturan Layanan</a>
        </span>
    </label>
    <div class="bg-red-500 text-white rounded shadow p-4 mt-4 flex items-center gap-2 text-sm" x-show="!tos" x-cloak
        x-transition>
        <i class="fa fa-warning"></i>
        <span>Kamu harus mencentang <a href="https://www.jagoanhosting.com/aturan-layanan/#tos" target="_blank"
                class="font-bold hover:underline">Aturan Layanan</a></span>
    </div>
</div>
