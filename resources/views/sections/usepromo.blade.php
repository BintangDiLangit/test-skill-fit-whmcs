<div class="p-6 flex flex-col gap-4" x-bind="usePromoWrapper" x-cloak>
    <h6 class="font-bold">Gunakan Kode Promo</h6>
    <div class="w-full flex gap-4">
        <input type="text" placeholder="Masukkan Kode Promo"
            class="flex-1 border border-default rounded text-sm py-3 px-4 w-full" x-bind="usePromoInput"
            x-model="promoCode" autocomplete="off">
        <button
            class="min-w-[100px] py-3 px-4 h-auto flex-none duration-200 text-sm bg-gray text-primary rounded flex items-center justify-center"
            x-bind="usePromoTrigger">
            <span class="loader border-white" x-show="isLoading" x-cloak></span>
            <span x-show="!isLoading">Pakai Promo</span>
        </button>
    </div>
</div>
<div class="p-6 flex flex-col gap-4" x-bind="appliedPromoWrapper" x-cloak>
    <div class="flex items-center gap-2">

        <div class="bg-secondary text-white p-1 px-2 rounded-full flex-none w-fit font-bold text-sm">
            <i class="fa fa-check-circle text-white"></i>
            KODE PROMO TERPASANG
        </div>
    </div>
    <div class="flex flex-col w-full">
        <div class="flex gap-4 justify-end p-4 flex-wrap bg-gray-faded rounded">
            <div class="flex flex-col justify-center gap-1 grow">
                <h6 class="font-bold text-2xl" x-bind="appliedPromoTitle"></h6>
                <p class="text-sm" x-bind="appliedPromoDescription"></p>
            </div>
            <div class="flex-none flex items-center w-full md:w-fit">
                <button
                    class="py-3 px-4 h-fit text-center flex-none duration-200 text-sm text-primary bg-white rounded w-full flex items-center justify-center gap-2 "
                    @click="removePromoCode">
                    <i class="fa fa-trash"></i>
                    <span x-show="!isLoading">Hapus Promo</span>
                </button>
            </div>
        </div>
    </div>
</div>
