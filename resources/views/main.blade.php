<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    @include('sections.head')
</head>

<body class="bg-gray-faded min-h-screen flex flex-col">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KRHZHST" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <nav class="bg-primary w-full flex-none">
        <div class="flex justify-between align-center py-3 max-w-screen-xl mx-auto px-4">
            <a href="https://member.jagoanhosting.com/clientarea.php" class="flex items-center">
                <img src="../../../member.jagoanhosting.com/templates/lagom2/assets/img/logo/logo_big.2091275115.png"
                    alt="" class="h-9">
            </a>
        </div>
    </nav>


    <div class="min-h-full w-full flex-1">
        <main>
            <div class="max-w-screen-xl mx-auto my-8 md:my-16 px-4">
                <div class="mb-6">
                    <h1 class="text-4xl font-normal font-karla text-primary">Order Layanan - Cloud Hosting Starter</h1>
                </div>
                <div class="flex flex-col lg:flex-row gap-6 relative">
                    <div class="flex flex-col gap-y-6 flex-auto w-full">
                        <div class="rounded border-primary shadow text-primary bg-white">
                            @include('sections.pilihpaket')
                        </div>
                        <div x-data="domainRegistration" class="rounded border-primary shadow text-primary bg-white z-10">
                            @include('sections.namadomain')
                        </div>


                        <div class="rounded border-primary shadow text-primary bg-white" x-data="promoSection">
                            @include('sections.usepromo')
                        </div>

                        <div x-data="authenticationForm" class="rounded border-primary shadow text-primary bg-white">
                            @include('sections.akun')
                        </div>
                    </div>
                    <div class="flex flex-col gap-4 sticky top-4 h-fit" x-data="cart">
                        @include('sections.ringkasanpesanan')
                    </div>
                </div>
        </main>
        <div class="hidden h-max w-max" x-data>
            <template x-teleport="body">
                <div class="fixed top-0 left-0 w-screen h-screen bg-black/10 backdrop-blur-sm flex items-center justify-center p-4 cursor-pointer z-[9999]"
                    x-show="Object.keys($store.CustomAlert).length > 0" x-cloak>
                    <div class="bg-white rounded shadow w-fit flex flex-col overflow-hidden max-w-sm min-w-[300px] cursor-auto"
                        @click.outside="CustomAlert.close()" x-show="Object.keys($store.CustomAlert).length > 0"
                        x-transition.in>
                        <template x-if="$store.CustomAlert.type === 'error'">
                            <div class="p-4 flex items-center justify-between bg-red-500 text-white">
                                <div class="flex items-center gap-2">
                                    <i class="fa fa-warning flex-none"></i>
                                    <h3 class="font-semibold"
                                        x-text="$store.CustomAlert?.header ?? 'Terjadi kesalahan'"></h3>
                                </div>
                                <button @click="CustomAlert.close()" x-show='$store.CustomAlert.closable'>
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </template>
                        <template x-if="$store.CustomAlert.type === 'loading'">
                            <div class="p-4 flex items-center justify-between bg-primary text-white">
                                <div class="flex items-center gap-2">
                                    <span class="loader"></span>
                                    <h3 class="font-semibold"
                                        x-text="$store.CustomAlert?.header ?? 'Sedang memproses...'"></h3>
                                </div>
                                <button @click="CustomAlert.close()" x-show='$store.CustomAlert.closable'>
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </template>
                        <template x-if="$store.CustomAlert.type === 'success'">
                            <div class="p-4 flex items-center justify-between bg-green-500 text-white">
                                <div class="flex items-center gap-2">
                                    <i class="fa fa-check-circle"></i>
                                    <h3 class="font-semibold" x-text="$store.CustomAlert?.header ?? 'Berhasil'"></h3>
                                </div>
                                <button @click="CustomAlert.close()" x-show='$store.CustomAlert.closable'>
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </template>
                        <div class="p-8 flex flex-col items-center">
                            <div class="flex flex-col items-center gap-6 mb-2">
                                <template x-if="$store.CustomAlert.type === 'error'">
                                    <i class="fa fa-warning flex-none text-5xl text-red-500"></i>
                                </template>
                                <template x-if="$store.CustomAlert.type === 'loading'">
                                    <span class="loader flex-none border-primary"
                                        style="width: 64px;height: 64px; border-width: 8px;"></span>
                                </template>
                                <template x-if="$store.CustomAlert.type === 'success'">
                                    <i class="fa fa-check-circle flex-none text-5xl text-green-500"></i>
                                </template>
                                <h3 class="text-2xl text-center font-bold w-full" x-text="$store.CustomAlert.title">
                                </h3>
                            </div>
                            <template x-for="description in $store.CustomAlert.descriptions">
                                <p class="text-center break-words" x-html='description'></p>
                            </template>
                        </div>

                    </div>
                </div>
            </template>
        </div>

    </div>
    <footer class="bg-white shadow text-sm w-full flex-none">
        <div class="max-w-screen-xl mx-auto flex items-center p-6 gap-4">
            <div class="text-primary flex-1 w-full">
                Copyright &copy; 2023 Bintang Miftaqul Huda. All Rights Reserved.
            </div>
        </div>
    </footer>

    @include('sections.script')

</body>

</html>
