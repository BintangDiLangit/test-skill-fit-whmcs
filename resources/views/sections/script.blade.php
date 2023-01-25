<link rel="stylesheet" href="{{ asset('assets/ajax/libs/font-awesome/6.2.0/css/all.min.css') }}"
    integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

<script src="{{ asset('assets/npm/axios/dist/axios.min.js') }}"></script>

<script>
    const AppliedDiscount = {
        items: [],
        action: {
            addToAppliedDiscount: (newElement) => {
                let isItemDuplicate = AppliedDiscount.action.isItemDuplicate(newElement.type)
                if (isItemDuplicate)
                    AppliedDiscount.action.removeItem(newElement.type)

                AppliedDiscount.items.push(newElement)

                AppliedDiscount.action.setAlpineDiscountItems()
            },
            isItemDuplicate: (value) => {
                return AppliedDiscount.items.some(i => i.type === value)
            },
            removeItem: (...values) => {
                AppliedDiscount.items = AppliedDiscount.items.filter(i => !values.includes(i.type))
                AppliedDiscount.action.setAlpineDiscountItems()
            },
            sortItem: () => {
                AppliedDiscount.items = AppliedDiscount.items.sort((a, b) => b.value - a.value)
            },
            setAlpineDiscountItems: () => {
                // Sort AppliedDiscountItems
                // AppliedDiscount.action.sortItem()
                // Reset Alpine store discountItems
                window.Alpine.store('discountItems').length = 0
                // Push new discount items to Alpine store discountItems
                window.Alpine.store('discountItems').push(...AppliedDiscount.items)
            }
        }
    }
    const Order = {
        checkMultipleWhoisDomains: async (domainExtensions, usePromiseAll = false) => {
            if (usePromiseAll)
                return await Promise.all(domainExtensions.split(',').map(async (domain) => Order
                    .checkWhoisDomain('fikrunamin' + domain)))

            return domainExtensions.split(',').map(async (domain) => Order.checkWhoisDomain('fikrunamin' +
                domain))
        },
        checkWhoisDomain: async (domainName) => await axios({
            method: 'get',
            url: `https://beli.jagoanhosting.com/api/whois/662?domain=${domainName}`,
            responseType: 'json'
        }).then(res => res).catch(err => err),
        generateHostingBillingCycle: (pricing) => {
            if (!pricing)
                return []

            const pricingNameConvention = {
                monthly: '1 Bulan',
                quarterly: '3 Bulan',
                semiannually: '6 Bulan',
                annually: '1 Tahun',
                biennially: '2 Tahun',
                triennially: '3 Tahun'
            }
            const pricingNameKeys = Object.keys(pricingNameConvention)
            const billingCycles = Object.entries(pricing).filter(value => pricingNameKeys.includes(value[0]))

            return billingCycles.map((value, i) => ({
                name: value[0],
                price: Number(value[1]),
                gimmickPrice: Order.calculateGimmickPrice(value[1], i),
                discount: Order.calculateBillingCycleDiscountPercentage(value[1], i)
            }))
        },
        validateFreeDomain: () => {
            const domain = Alpine.store('cart').items.find(i => i.type === 'Domain')
            const hosting = Alpine.store('cart').items.find(i => i.type === 'Hosting')

            if (!domain)
                return

            let isFreeDomainBillingCycle = Order.getFreeDomainBillingCycle(hosting.billingCycle)
            let isFreeDomainExtension = Order.getFreeDomainExtension(domain.ext)

            if (isFreeDomainBillingCycle && isFreeDomainExtension) {
                CartValue.action.addToCartValue({
                    ...domain,
                    price: 0,
                    totalDiscountValue: domain.gimmickPrice
                })

                AppliedDiscount.action.addToAppliedDiscount({
                    type: "Domain",
                    name: `Diskon Domain`,
                    value: domain.gimmickPrice
                })
                return
            }

            AppliedDiscount.action.removeItem('Domain')
            CartValue.action.addToCartValue({
                ...domain,
                price: domain.gimmickPrice,
                totalDiscountValue: 0
            })
        },
        renderIDRValue: (value, returnFreeOnZeroPrice = true) => {
            if (value === 0 && returnFreeOnZeroPrice)
                return 'FREE'

            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(value <= 0 ? 0 : value)
        },
        setPromoValue() {
            const rawpromo = Alpine.store('discountItems').find(i => i.type === 'rawpromo')
            const hosting = Alpine.store('cart').items.find(i => i.type === 'Hosting')
            const domain = Alpine.store('cart').items.find(i => i.type === 'Domain')

            if (!rawpromo)
                return

            const promoDomainCycle = rawpromo.cycles.toLowerCase().replaceAll('-', '').split(',').filter(cycle =>
                cycle.includes('years'))
            const promoHostingCycle = rawpromo.cycles.toLowerCase().replaceAll('-', '').split(',').filter(cycle => !
                cycle.includes('years'))

            let validDomainCycle = promoDomainCycle.some(cycle => {
                const domainCycleConventionNames = {
                    "1 tahun": "1years",
                    "2 tahun": "2years",
                    "3 tahun": "3years",
                    "4 tahun": "4years",
                    "5 tahun": "5years",
                    "6 tahun": "6years",
                    "7 tahun": "7years",
                    "8 tahun": "8years",
                    "9 tahun": "9years",
                }

                return cycle === domainCycleConventionNames[(domain?.billingCycle ?? null)]
            })

            let validHostingCycle = promoHostingCycle.some(cycle => cycle === hosting?.billingCycle)

            if (rawpromo.cycles !== '' && ((promoDomainCycle.length > 0 && !validDomainCycle) || (promoHostingCycle
                    .length > 0 && !validHostingCycle))) {
                CartValue.action.removeItem(3)
                AppliedDiscount.action.removeItem('rawpromo', 'Promo')
                return
            }

            if (rawpromo.cycles !== '' && domain && rawpromo.appliesto.some(i => i[0] === 'D')) {
                if (rawpromo.requires.join('').length > 0) {
                    if (domain.billingCycleType !== 'register' ||
                        !rawpromo.requires.some(i => i.slice(1) === domain.ext || i === '662')) {
                        CartValue.action.removeItem(3)
                        AppliedDiscount.action.removeItem('rawpromo', 'Promo')
                        return
                    }
                }

                if (domain.billingCycleType !== 'register' ||
                    !rawpromo.appliesto.some(i => i.slice(1) === domain.ext)) {
                    CartValue.action.removeItem(3)
                    AppliedDiscount.action.removeItem('rawpromo', 'Promo')
                    return
                }
            }

            let promoValue = rawpromo.value
            let promoValueHosting = 0,
                promoValueDomain = 0

            if (rawpromo.appliesto.includes('662'))
                if (rawpromo.promoType === "Percentage")
                    promoValueHosting = hosting.price * 1.11 * Number(rawpromo.value) / 100
            else
                promoValueHosting = rawpromo.value * 1.11

            if (rawpromo.appliesto.some(i => i.slice(1) === domain?.ext) && domain.price !== 0)
                if (rawpromo.promoType === "Percentage")
                    promoValueDomain = domain.price * 1.11 * Number(rawpromo.value) / 100
            else
                promoValueDomain = rawpromo.value * 1.11

            promoValue = promoValueHosting + promoValueDomain

            AppliedDiscount.action.addToAppliedDiscount({
                type: "Promo",
                name: "Diskon Kode Promo",
                value: promoValue,
            })
        },
        renderBillingCycleToHumanReadable: (value) => {
            const rule = {
                annually: 'tahunan',
                biennially: '2 tahunan',
                triennially: '3 tahunan',
                monthly: 'bulanan',
                quarterly: '3 bulanan',
                semiannually: '6 bulanan'
            }

            return rule[value]
        },
        generateHumanReadableActiveBillingCycles(data, domainType) {
            const rule = {
                1: '1 Tahun',
                2: '2 Tahun',
                3: '3 Tahun',
                4: '4 Tahun',
                5: '5 Tahun',
                6: '6 Tahun',
                7: '7 Tahun',
                8: '8 Tahun',
                9: '9 Tahun'
            }

            data = domainType === 'Transfer' ? data['transfer'] : data['register']

            return Object.entries(data).map(dt => ({
                label: rule[dt[0]],
                price: domainType !== 'Use' ? Number(dt[1]) : '-'
            })).filter(dt => dt.price > 0)
        },
        calculateGimmickPrice: (actualPrice, index) => {
            const defaultPrice = 120000
            let multiplier = 1

            if (index >= 3)
                multiplier = 12 * (index - 2)
            else if (index === 2)
                multiplier = 6
            else if (index === 1)
                multiplier = 3

            const gimmickPrice = defaultPrice * multiplier
            return gimmickPrice
        },
        calculateBillingCycleDiscountPercentage: (actualPrice, index) => {
            const defaultPrice = 120000
            let multiplier = 1

            if (index >= 3)
                multiplier = 12 * (index - 2)
            else if (index === 2)
                multiplier = 6
            else if (index === 1)
                multiplier = 3

            const gimmickPrice = defaultPrice * multiplier
            return Math.ceil((gimmickPrice - Number(actualPrice)) / gimmickPrice * 100)
        },
        generateAvailableDomainExtensions(data) {
            return Object.entries(data).map(ext => ({
                name: '.' + ext[0],
                conf: ext[1],
            }))
        },
        getFreeDomainBillingCycle: (billingCycle) => {
            const pricingNameConvention = ['triennially', 'biennially', 'annually']
            return pricingNameConvention.includes(billingCycle.toLowerCase());
        },
        getFreeDomainExtension: (ext) => {
            const freeDomainExt = [".MY.ID", ".BIZ.ID", ".WEB.ID"];
            return freeDomainExt.includes(ext.toUpperCase());
        },
        isInvalidRegistrationInput: () => {
            let errors = []
            const registrastionInput = document.querySelectorAll('#registration-form input')
            Array.from(registrastionInput).forEach(node => {
                if (node.checkValidity())
                    return

                if (node.validity.valueMissing)
                    return errors.push(`<strong class="text-red-500">${node.name} wajib diisi</strong>`)

                if (!node.validity.valid)
                    return errors.push(`<strong class="text-red-500">${node.name} tidak valid</strong>`)
            })

            return errors
        },
        getQueryParam(param) {
            const params = new Proxy(new URLSearchParams(window.location.search), {
                get: (searchParams, prop) => searchParams.get(prop),
            });

            return params[param]
        },
        generatePricingNameConventions: (pricing) => {
            const pricingNameConvention = {
                monthly: '1 Bulan',
                quarterly: '3 Bulan',
                semiannually: '6 Bulan',
                annually: '1 Tahun',
                biennially: '2 Tahun',
                triennially: '3 Tahun'
            }
            const pricingNameKeys = Object.keys(pricingNameConvention)
            const pricings = Object.entries(pricing).filter(value => pricingNameKeys.includes(value[0]))

            return pricings
        },
        generateConfigOptions: (config) => {
            if (!config || config.configoption.length <= 0)
                return []

            return config.configoption.map(conf => ({
                id: conf.id,
                name: conf.name,
                type: conf.type,
                options: conf.options.option.map(opt => ({
                    id: opt.id,
                    name: opt.name,
                    pricing: Order.generatePricingNameConventions(opt.pricing.IDR),
                }))
            }))
        },
        generateCustomFields: (customfields) => {
            if (!customfields || customfields.customfield.length <= 0)
                return []

            return customfields.customfield
        },
        generateRandomString(length) {
            let result = '';
            let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let charactersLength = characters.length;
            for (let i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        }
    }
    const CartValue = {
        userDetail: {
            userStatus: 'login',
            userData: {



            }
        },
        items: [],
        action: {
            addToCartValue: (newElement) => {
                let isItemDuplicate = CartValue.action.isItemDuplicate(newElement)
                if (isItemDuplicate)
                    CartValue.action.removeItem(newElement.id)

                CartValue.items.push(newElement)
                CartValue.action.setAlpineCartItems()
            },
            isItemDuplicate: (newElement) => {
                return CartValue.items.some(i => i.id === newElement.id)
            },
            removeItem: (...values) => {
                CartValue.items = CartValue.items.filter(i => !values.includes(i.id))
                CartValue.action.setAlpineCartItems()
            },
            sortItem: () => {
                CartValue.items = CartValue.items.sort((a, b) => a.id - b.id)
            },
            setAlpineCartItems: () => {
                // Sort AppliedDiscountItems
                CartValue.action.sortItem()
                // Reset Alpine store discountItems
                Alpine.store('cart').setItems(CartValue.items)
            }
        }
    }
    const CartAddons = {
        items: {},
        action: {
            addToCartAddons: (key, newElement, duplicateCondition = {
                paramKey: 'type',
                value: newElement.type
            }) => {
                const isItemDuplicate = CartAddons.action.isItemDuplicate(key, newElement)
                if (isItemDuplicate)
                    CartAddons.action.removeItem(key, duplicateCondition.paramKey, duplicateCondition.value)

                if (!CartAddons.items[key])
                    CartAddons.items[key] = []

                CartAddons.items[key].push(newElement)
                CartAddons.action.setAlpineCartItems()
            },
            isItemDuplicate: (key, newElement) => {
                return CartAddons.items[key]?.some(i => i.type === newElement.type)
            },
            removeItem: (key, paramKey, value) => {
                CartAddons.items[key] = CartAddons.items[key]?.filter(i => i[paramKey] !== value)
                CartAddons.action.setAlpineCartItems()
            },
            setAlpineCartItems: () => {
                Alpine.store('cartAddons').setItems(CartAddons.items)
            },
        }
    }
</script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('cart', {
            items: [],
            setItems(items) {
                this.items = []
                this.items = items
            },
        })
        Alpine.store('cartAddons', {
            items: {},
            setItems(items) {
                this.items = {}
                this.items = items
            },
            get getItems() {
                if (Object.keys(this.items).length <= 0)
                    return []

                return Object.entries(this.items).map(i => i[1]).reduce((a, b) => a.concat(b))
            }
        })
        Alpine.store('discountItems', [])
        Alpine.store('userDetails', {
            status: 'login',
            data: {



            }
        })
    })
</script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('billingCycle', () => ({
            isLoading: true,
            show: false,
            discount: 0,
            billingCycles: [],
            configOptions: [],
            availableConfigOptions: [],
            availableCustomFields: [],
            selectedBillingCycle: {
                index: 0,
                name: {
                    ['x-text']() {
                        return !this.isLoading ?
                            Order.renderBillingCycleToHumanReadable(this.billingCycles[this
                                .selectedBillingCycle.index].name) :
                            ''
                    }
                },
                price: {
                    ['x-text']() {
                        return !this.isLoading ?
                            Order.renderIDRValue(this.billingCycles[this.selectedBillingCycle.index]
                                .price) :
                            ''
                    }
                },
                discount: {
                    ['x-text']() {
                        return !this.isLoading ?
                            this.billingCycles[this.selectedBillingCycle.index].discount + '% OFF' :
                            ''
                    },
                    ['x-show']() {
                        return !this.isLoading ?
                            this.billingCycles[this.selectedBillingCycle.index].discount > 0 :
                            false
                    }
                },
                gimmickPrice: {
                    ['x-text']() {
                        return !this.isLoading ?
                            Order.renderIDRValue(this.billingCycles[this.selectedBillingCycle.index]
                                .gimmickPrice) :
                            ''
                    },
                    ['x-show']() {
                        return !this.isLoading ?
                            this.billingCycles[this.selectedBillingCycle.index].discount > 0 :
                            false
                    }
                },
            },
            async init() {
                await axios({
                    method: 'get',
                    url: `https://beli.jagoanhosting.com/api/product/662`,
                    responseType: 'json'
                }).then(response => {
                    const availableCustomFields = Order.generateCustomFields(response
                        .data[0].customfields)
                    const availableConfigOptions = Order.generateConfigOptions(response
                        .data[0].configoptions)
                    const availableBillingCycles = Order.generateHostingBillingCycle(
                        response.data[0].pricing.IDR)

                    this.availableCustomFields = availableCustomFields
                    this.availableConfigOptions = availableConfigOptions
                    this.billingCycles = availableBillingCycles.filter(i => Number(i
                        .price) > 0)

                    let billingCycleQueryParam = Order.getQueryParam('billingcycle')
                    let billingCycleIndex = this.billingCycles.findIndex(i => i.name ===
                        'triennially')

                    if (this.billingCycles.findIndex(i => i.name ===
                            billingCycleQueryParam) !== -1)
                        billingCycleIndex = this.billingCycles.findIndex(i => i.name ===
                            billingCycleQueryParam)

                    this.setActiveBillingCycle(billingCycleIndex)
                    this.isLoading = false
                })
            },
            setActiveBillingCycle(index) {
                let gimmickPrice = this.billingCycles[index].gimmickPrice
                let actualPrice = this.billingCycles[index].price
                let billingCycleDiscount = this.billingCycles[index].discount

                AppliedDiscount.action.removeItem("Hosting")

                const hosting = CartValue.items.find(item => item.type === 'Hosting')
                if (!hosting)
                    CartValue.action.addToCartValue({
                        id: 1,
                        type: 'Hosting',
                        price: actualPrice,
                        billingCycle: this.billingCycles[index].name,
                        billingCycleValue: this.billingCycles[index].name,
                        configOptions: this.configOptions,
                        // Additional fields
                        name: 'S Entry - S2',
                        gimmickPrice: gimmickPrice,
                        defaultPrice: actualPrice,
                        totalDiscountValue: gimmickPrice - actualPrice,
                        description: Order.renderBillingCycleToHumanReadable(this.billingCycles[
                            index].name),
                        availableConfigOptions: this.availableConfigOptions,
                        availableCustomFields: this.availableCustomFields,
                    })
                else
                    CartValue.action.addToCartValue({
                        ...hosting,
                        id: 1,
                        type: 'Hosting',
                        price: actualPrice,
                        billingCycle: this.billingCycles[index].name,
                        billingCycleValue: this.billingCycles[index].name,
                        configOptions: this.configOptions,
                        // Additional fields
                        name: 'S Entry - S2',
                        gimmickPrice: gimmickPrice,
                        defaultPrice: actualPrice,
                        totalDiscountValue: gimmickPrice - actualPrice,
                        description: Order.renderBillingCycleToHumanReadable(this.billingCycles[
                            index].name),
                        availableConfigOptions: this.availableConfigOptions,
                        availableCustomFields: this.availableCustomFields,
                    })

                if (gimmickPrice - actualPrice > 0) {
                    AppliedDiscount.action.addToAppliedDiscount({
                        type: "Hosting",
                        name: `Diskon Hosting (${billingCycleDiscount}%)`,
                        value: (gimmickPrice - actualPrice) * 1.11
                    })
                }
                Order.setPromoValue()
                Order.validateFreeDomain()
                this.selectedBillingCycle.index = index
            }
        }))
    })
</script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('availableDomainExtensions', {
            items: []
        })
        Alpine.data('domainRegistration', () => ({
            only: ([]),
            isLoading: true,
            isAdding: false,
            showAvailableExtensions: false,
            showAvailableBillingCycles: false,
            availableExtensions: [],
            searchKeyword: '',
            activeExtension: {},
            domainName: '',
            isAvailable: true,
            isTransferable: false,
            domainType: 'Register',
            checkWhois: true,
            activeBillingCycleIndex: 0,
            rules: '',
            billingCycles: [],
            activeBillingCycle: {},
            eppCode: '',
            domainTerms: {
                ".co.id": [{
                    "id": 1,
                    "domain": ".co.id",
                    "subtitle": "Peruntukan Domain",
                    "terms_and_conditions": [
                        "Badan usaha\/organisasi\/entitas bisnis, atau sejenisnya yang berbadan hukum dan beroperasi di Indonesia."
                    ]
                }, {
                    "id": 9,
                    "domain": ".co.id",
                    "subtitle": "Dokumen Pendaftaran untuk Perusahaan",
                    "terms_and_conditions": ["KTP Republik Indonesia",
                        "SIUP\/TDP\/AKTA\/Surat Izin yang setara",
                        "Sertifikat Merek (bila ada)",
                        "[Download Surat Keterkaitan]<https:\/\/member.jagoanhosting.com\/dl.php?type=d&id=97>"
                    ]
                }],
                ".web.id": [{
                    "id": 2,
                    "domain": ".web.id",
                    "subtitle": "Dokumen Pendaftaran untuk Personal",
                    "terms_and_conditions": ["KTP Republik Indonesia"]
                }],
                ".sch.id": [{
                    "id": 3,
                    "domain": ".sch.id",
                    "subtitle": "Peruntukan Domain",
                    "terms_and_conditions": [
                        "Lembaga pendidikan yang menyelenggarakan pendidikan seperti TK, SD, SMP, SMU\/SMK"
                    ]
                }, {
                    "id": 4,
                    "domain": ".sch.id",
                    "subtitle": "Dokumen Pendaftaran untuk Pendidikan non-formal yang diakui oleh SKPD",
                    "terms_and_conditions": [
                        "SK Pendirian Lembaga dari Kementerian atau SKPD terkait.",
                        "KTP Republik Indonesia"
                    ]
                }, {
                    "id": 8,
                    "domain": ".sch.id",
                    "subtitle": "Dokumen Pendaftaran untuk Sekolah Resmi",
                    "terms_and_conditions": [
                        "Surat Keterangan Kepala Sekolah atau Kepala Lembaga. Draft surat keterangan Kepala Sekolah",
                        "KTP Republik Indonesia",
                        "[Download Surat Kuasa Kepala Sekolah]<https:\/\/member.jagoanhosting.com\/dl.php?type=d&id=89>",
                        "[Surat Permohonan Sekolah]<https:\/\/member.jagoanhosting.com\/dl.php?type=d&id=88>"
                    ]
                }],
                ".ac.id": [{
                    "id": 5,
                    "domain": ".ac.id",
                    "subtitle": "Peruntukan Domain",
                    "terms_and_conditions": [
                        "Akademik\/perguruan Institusi tinggi Indonesia, min D1"
                    ]
                }, {
                    "id": 10,
                    "domain": ".ac.id",
                    "subtitle": "Dokumen Pendaftaran untuk Universitas",
                    "terms_and_conditions": ["KTP Republik Indonesia.",
                        "SK Pendirian Lembaga dari Kementerian\/Lembaga yang berwenang sesuai Peraturan Perundang-Undangan",
                        "Surat Keterangan Rektor atau Pimpinan Lembaga",
                        "[Download Surat Permohonan Lembaga Pendidikan]<https:\/\/member.jagoanhosting.com\/dl.php?type=d&id=92>"
                    ]
                }],
                ".or.id": [{
                    "id": 6,
                    "domain": ".or.id",
                    "subtitle": "Peruntukan Domain",
                    "terms_and_conditions": [
                        "Organisasi \/ yayasan \/ perkumpulan \/ komunitas\t"
                    ]
                }, {
                    "id": 11,
                    "domain": ".or.id",
                    "subtitle": "Dokumen Pendaftaran untuk Organisasi",
                    "terms_and_conditions": ["KTP Republik Indonesia.",
                        "Akta Notaris atau Surat Keterangan dari organisasi yang bersangkutan.[Download Surat Kuasa Pimpinan Organisasi\/Lembaga]<https:\/\/member.jagoanhosting.com\/dl.php?type=d&id=87>"
                    ]
                }],
                ".ponpes.id": [{
                    "id": 7,
                    "domain": ".ponpes.id",
                    "subtitle": "Peruntukan Domain",
                    "terms_and_conditions": ["Pondok pesantren"]
                }, {
                    "id": 12,
                    "domain": ".ponpes.id",
                    "subtitle": "Dokumen Pendaftaran untuk Pesantren",
                    "terms_and_conditions": [
                        "Surat keterangan pimpinan pondok pesantren atau pimpinan lembaga tentang prihal pendaftaran nama domain.",
                        "KTP yang masih berlaku",
                        " [Download Surat Kuasa Kepala Sekolah]<https:\/\/member.jagoanhosting.com\/dl.php?type=d&id=89>"
                    ]
                }],
                ".biz.id": [{
                    "id": 13,
                    "domain": ".biz.id",
                    "subtitle": "Peruntukan Domain",
                    "terms_and_conditions": [
                        "Pelaku bisnis yang tidak memiliki badan hukum seperti warung, toko online, dll."
                    ]
                }]
            },
            async init() {
                this.$watch('availableExtensions', items => {
                    if (items.length <= 0)
                        return

                    const domainType = Order.getQueryParam('domain')
                    const sld = Order.getQueryParam('sld')
                    const tld = Order.getQueryParam('tld')

                    const indexExt = items.findIndex(item => item.name === tld)

                    this.domainType = domainType === 'transfer' ? 'Transfer' :
                        'Register'
                    if (sld && sld !== '')
                        this.domainName = sld
                    if (tld && tld !== '') {
                        this.activeExtension = this.availableExtensions.filter(i => i
                            .name.includes(this.searchKeyword.toLowerCase()))[
                            indexExt]
                        this.billingCycles = Order
                            .generateHumanReadableActiveBillingCycles(this
                                .activeExtension.conf, this.domainType)
                    }

                    if (domainType === 'register' && sld !== '')
                        this.addToCart()
                })

                await axios({
                    method: 'get',
                    url: `https://beli.jagoanhosting.com/api/get-tld-domain`,
                    responseType: 'json'
                }).then(response => {
                    if (response.status !== 200 || response.data.code === 0)
                        return CustomAlert.open({
                            type: 'error',
                            title: 'Gagal mengambil data domain.',
                            closable: true,
                            descriptions: ['Coba di-reload halamannya, Sob!']
                        })

                    const availableDomainExtensions = Order
                        .generateAvailableDomainExtensions(response.data)
                    this.availableExtensions = availableDomainExtensions;
                    this.$store.availableDomainExtensions.items =
                        availableDomainExtensions

                    if (this.only.length > 0)
                        this.availableExtensions = this.availableExtensions.filter(
                            ext => this.only.includes(ext.name))

                    // Set default domain ext ke index 0
                    this.setActiveExtension(0, false)
                    this.isLoading = false
                })

            },
            toggleAvailableExtensions() {
                if (this.availableExtensions.length <= 0)
                    return

                this.showAvailableExtensions = !this.showAvailableExtensions

                this.$nextTick(() => this.$refs.domainSearchInput.focus())
            },
            toggleAvailableBillingCycles() {
                if (this.billingCycles.length <= 0)
                    return

                this.showAvailableBillingCycles = !this.showAvailableBillingCycles
            },
            setActiveExtension(index, enableToggle = true, addToCart = true) {
                if (enableToggle)
                    this.toggleAvailableExtensions()

                this.activeExtension = this.availableExtensions.filter(i => i.name.includes(this
                    .searchKeyword.toLowerCase()))[index]
                this.setBillingCycles(true)
                this.searchKeyword = ''

                if (this.domainName !== '')
                    this.addToCart()
            },
            setBillingCycles(reset = false) {
                this.billingCycles = Order.generateHumanReadableActiveBillingCycles(this
                    .activeExtension.conf, this.domainType)
                if (reset) {
                    this.activeBillingCycle = this.billingCycles[0]
                    return
                }

                this.activeBillingCycle = this.billingCycles[this.activeBillingCycleIndex]
            },
            setActiveBillingCycle(index) {
                this.toggleAvailableBillingCycles()
                this.activeBillingCycleIndex = index
                this.activeBillingCycle = this.billingCycles[index]

                if (this.domainName !== '')
                    this.addToCart();
            },
            validateRules() {
                let errors = []
                const rules = this.rules.split(',').map(rule => ({
                    label: rule.split(':')[0],
                    value: rule.split(':')[1] ?? null,
                }))


                const rule = (label) => rules.find(item => item.label === label)

                if (min = rule('min')) {
                    if (this.domainName.replaceAll(this.activeExtension.name, '').length < min
                        .value)
                        errors.push('Domain minimal terdiri dari 5 karakter!')
                }

                return errors
            },
            async addToCartItems(popupOnChange) {
                await CartValue.action.removeItem(2)
                await AppliedDiscount.action.removeItem('Domain')

                if (this.rules.length > 0) {
                    if (messages = this.validateRules()) {
                        if (messages.length > 0)
                            return CustomAlert.open({
                                type: 'error',
                                title: 'Domain gagal dimasukkan ke keranjang, Sob!',
                                closable: true,
                                descriptions: messages
                            })
                    }
                }

                if (this.domainType !== 'Use')
                    this.domainName = this.domainName.replaceAll(/[^0-9a-zA-Z\-]/gi, '')

                if (this.domainType === 'Use')
                    this.domainName = this.domainName.replaceAll(/[^0-9a-zA-Z\-\.]/gi, '')

                if (!popupOnChange && this.domainName === '')
                    return

                if (this.availableExtensions.length <= 0 || this.domainName === '')
                    return CustomAlert.open({
                        type: 'error',
                        title: 'Masukkan domain terlebih dahulu, Sob!',
                        closable: true,
                        descriptions: [
                            'Tambahkan domain dulu sebelum melakukan pengecekan domain, Sob!'
                        ]
                    })

                if (this.domainType.toLowerCase() === 'register') {
                    if (this.domainName.match(/[^0-9a-zA-Z\-\_]/gi))
                        return CustomAlert.open({
                            type: 'error',
                            title: 'Oops!',
                            closable: true,
                            descriptions: [
                                'Domain yang kamu masukkan mengandung karakter yang tidak sesuai.',
                                'Pastikan domain kamu hanya terdiri dari huruf, angka, dan simbol dash (-), Sob!'
                            ]
                        })
                }
                const domainInCartAddons = this.$store?.cartAddonsDomainTemp?.some(domain =>
                    domain === this.domainName + this.activeExtension.name)
                if (domainInCartAddons && this.$store?.bundlingDomainMyId)
                    return this.isAvailable = false;

                if (this.domainType.toLowerCase() === 'use')
                    if (this.domainName.match(/[^0-9a-zA-Z\-\_\.]/gi))
                        return CustomAlert.open({
                            type: 'error',
                            title: 'Oops!',
                            closable: true,
                            descriptions: [
                                'Domain yang kamu masukkan mengandung karakter yang tidak sesuai.',
                                'Pastikan domain kamu hanya terdiri dari huruf, angka, dan simbol dash (-) dan titik(.), Sob!'
                            ]
                        })

                let domainPrice = 0,
                    domainGimmickPrice = 0;

                if (this.domainType === 'Register') {
                    let isFreeDomainBillingCycle = false
                    let isFreeDomainExtension = false
                    if (Alpine.store('cart').items.some(i => i.type === 'Hosting')) {
                        isFreeDomainBillingCycle = Order.getFreeDomainBillingCycle(Alpine.store(
                            'cart').items.find(i => i.type === 'Hosting').billingCycle)
                        isFreeDomainExtension = Order.getFreeDomainExtension(this
                            .activeExtension.name)
                    }

                    domainGimmickPrice = Number(this.activeBillingCycle.price);
                    domainPrice = isFreeDomainBillingCycle && isFreeDomainExtension ? 0 :
                        domainGimmickPrice;
                }

                if (this.domainType === 'Transfer') {
                    domainGimmickPrice = Number(this.activeBillingCycle.price);
                    domainPrice = domainGimmickPrice;

                    if (this.eppCode === '')
                        return
                }

                let domainName = this.domainName + this.activeExtension.name

                if (this.checkWhois && this.domainType !== 'Use') {
                    let response = await Order.checkWhoisDomain(domainName)

                    if (response && !response.data?.whois?.available && this.domainType ===
                        'Register')
                        return this.isAvailable = false

                    if (this.domainType === 'Transfer' && this.eppCode === '')
                        return this.isTransferable = false

                    if (this.domainType === 'Transfer' && this.eppCode !== '') {
                        if (response.data?.whois?.available)
                            return this.isTransferable = false

                        this.isTransferable = true
                    }

                    this.isAvailable = true
                }

                CartValue.action.addToCartValue({
                    id: 2,
                    name: domainName.toLowerCase(),
                    type: 'Domain',
                    price: domainPrice,
                    billingCycle: this.activeBillingCycle?.label.toLowerCase() ?? '',
                    billingCycleType: this.domainType.toLowerCase(),
                    eppCode: this.domainType === 'Transfer' ? this.eppCode : '',
                    // Additional fields
                    title: `Domain ${this.domainType} -`,
                    gimmickPrice: domainGimmickPrice,
                    totalDiscountValue: domainGimmickPrice - domainPrice,
                    description: domainName.toLowerCase(),
                    ext: this.activeExtension.name,
                })

                if (domainGimmickPrice - domainPrice > 0) {
                    AppliedDiscount.action.addToAppliedDiscount({
                        type: "Domain",
                        name: `Diskon Domain`,
                        value: (domainGimmickPrice - domainPrice) * 1.11
                    })
                }
            },
            async addToCart(popupOnChange = true, resetBillingCycle = false) {
                this.setBillingCycles(resetBillingCycle)
                this.isAdding = true
                await this.addToCartItems(popupOnChange)
                Order.setPromoValue()
                this.isAdding = false
            },
            get isDomainExistInCart() {
                return Alpine.store('cart').items.some(i => i.type === 'Domain')
            },
            getDomainGroupColor(label) {
                if (label === 'new')
                    return 'bg-green-100 text-green-500'
                if (label === 'sale')
                    return 'bg-purple-400 text-white'

                return 'bg-red-100 text-red-500'
            },
            transformText(text) {
                // Generate link from text
                let availableLinks = text.match(/(\[[\w\s]+\])(\<[\S]+\>)/gi)
                if (availableLinks) {
                    let generatedLinks = availableLinks.map(link => {
                        let label = link.match(/(\[[\w\s]+\])/gi)[0].replaceAll(/[\[\]]/gi,
                            '')
                        let url = link.match(/(\<[\S]+\>)/gi)[0].replaceAll(/[\<\>]/gi, '')

                        return `<a class="text-blue-500 group" href="${url}" target="_blank" rel="noopener noreferrer">
      <span class="group-hover:underline">${label}</span>
      <i class="fas fa-external-link text-xs"></i>
    </a>`
                    })
                    availableLinks.forEach((element, index) => {
                        text = text.replaceAll(element, generatedLinks[index])
                    });
                }

                return text
            }
        }))
        Alpine.data('crossSaleHosting', () => ({
            domain: null,
            crossSaleRedirectLink: {
                ['x-bind:href']() {
                    if (!this.domain)
                        return '#'

                    const product = this.crossSaleProducts.find(product => product.free_domains
                        .split(',').includes(this.domain.ext))
                    return `${product?.link}?domain=register&sld=${this.domain?.name?.split('.')[0]}&tld=${this.domain?.ext}&billingcycle=annually`
                }
            },
            crossSaleDescription: {
                ['x-html']() {
                    if (!this.domain)
                        return ''

                    const product = this.crossSaleProducts.find(product => product.free_domains
                        .split(',').includes(this.domain.ext))
                    return `<span class="bg-orange-500 text-white px-2 py-1 rounded font-bold">DOMAIN INI GRATIS!</span> dengan menambahkan paket hosting ${product?.name} (tahunan)`
                }
            },
            get crossSaleProducts() {
                const products = JSON.parse('[]')

                return products
            },
            get showCrossSale() {
                if (!this.domain)
                    return false

                return Alpine.store('cart').items.some(i => i.type === 'Domain' && i
                        .billingCycleType === 'register') &&
                    this.crossSaleProducts.some(product => product.free_domains.split(',')
                        .includes(this.domain.ext))
            },
            init() {
                this.$watch('$store.cart.items', items => {
                    const domain = items.find(i => i.type === 'Domain');
                    if (!domain) return;
                    this.domain = domain;
                })
            }
        }))
    })
</script>
<script>
    let initCounterPromoCode = 0

    document.addEventListener('alpine:init', () => {
        Alpine.data('promoSection', () => ({
            appliedPromoCode: '',
            promoCode: '',
            isLoading: false,
            usePromoWrapper: {
                ['x-show']() {
                    return !Alpine.store('discountItems').some(i => i.type === 'Promo')
                }
            },
            usePromoInput: {
                ['@keyup.enter']() {
                    this.checkPromoCode()
                }
            },
            usePromoTrigger: {
                ['@click']() {
                    this.checkPromoCode()
                }
            },
            appliedPromoWrapper: {
                ['x-show']() {
                    return Alpine.store('discountItems').some(i => i.type === 'Promo')
                }
            },
            appliedPromoTitle: {
                ['x-text']() {
                    return this.appliedPromoCode.toUpperCase()
                }
            },
            appliedPromoDescription: {
                ['x-html']() {
                    const promo = Alpine.store('discountItems').find(i => i.type === 'rawpromo')
                    if (promo?.description)
                        return promo.description

                    if (promo?.promoType === 'Percentage')
                        return `Potongan ${Number(promo?.value)}% Satu Kali Pembayaran Diskon.`

                    return `Potongan ${Order.renderIDRValue(promo?.value)} Satu Kali Pembayaran Diskon.`
                }
            },
            initCounterPromoCode: 0,
            init() {
                if (code = Order.getQueryParam('promocode'))
                    this.promoCode = code

                this.$watch('$store.cart.items', items => {
                    if (code = Order.getQueryParam('promocode')) {
                        const hosting = Alpine.store('cart').items.find(item => item
                            .type === 'Hosting')
                        const domain = Alpine.store('cart').items.find(item => item.type ===
                            'Domain')

                        if ((!hosting && '662' !== '0') || !domain || this
                            .initCounterPromoCode > 0) return;

                        this.usePromoCode(code)
                        this.initCounterPromoCode++
                    }
                })
            },
            async checkPromoCode() {
                try {
                    this.isLoading = true
                    this.removePromoCode()

                    if (this.promoCode === '')
                        throw ['Kode promo tidak boleh kosong, Sob!']

                    await axios
                        .get('https://beli.jagoanhosting.com/api/promo/' + this.promoCode)
                        .then(res => {
                            let promoData = res.data
                            let errors = this.validatePromo(promoData)

                            if (errors.length > 0)
                                throw errors

                            promoData = promoData[0]

                            if (matchedAffId = promoData.notes.match(/%%[\w]+%%/gmi)) {
                                const affId = matchedAffId[0].replaceAll('%', '')
                                CartValue.action.addToCartValue({
                                    ...CartValue.items[0],
                                    affId
                                })
                            }

                            let notes = promoData.notes.match(/\{\{.+\}\}/gi)?.[0]?.slice(2,
                                -2).trim() ?? ''

                            AppliedDiscount.action.addToAppliedDiscount({
                                type: "rawpromo",
                                promoType: promoData.type,
                                name: "Diskon Kode Promo",
                                description: notes,
                                value: promoData.value,
                                cycles: promoData.cycles.toLowerCase().replaceAll(
                                    '-', ''),
                                requires: promoData.requires.split(','),
                                appliesto: promoData.appliesto.split(','),
                            })

                            CartValue.action.addToCartValue({
                                id: 3,
                                type: 'Promo',
                                promoCode: this.promoCode
                            })

                            Order.setPromoValue()
                            this.appliedPromoCode = this.promoCode
                        })
                } catch (error) {
                    if (error.response?.status === 404)
                        error = ['Kode promo tidak valid, Sob!']

                    CustomAlert.open({
                        type: 'error',
                        title: 'Kode promo gagal terpasang, Sob!',
                        descriptions: error,
                        closable: true
                    })
                } finally {
                    this.isLoading = false;
                }
            },
            async usePromoCode(code) {
                this.promoCode = code
                this.checkPromoCode()
            },
            removePromoCode() {
                CartValue.action.removeItem(3)
                AppliedDiscount.action.removeItem('Promo', 'rawpromo')
                this.appliedPromoCode = ''
            },
            validatePromo(promoData) {
                const hosting = Alpine.store('cart').items.find(i => i.type === 'Hosting')
                const domain = Alpine.store('cart').items.find(i => i.type === 'Domain')
                let errors = []

                if (CartValue.items.length <= 0) {
                    errors.push('Keranjang belanjamu masih kosong')
                    return errors
                }

                if (promoData?.code === 0 || promoData.length === 0) {
                    errors.push(promoData?.message ?? 'Kode promo tidak valid, Sob!')
                    return errors
                }

                promoData = promoData[0]

                if (promoData.appliesto.split(',').some(p => p[0] === 'D'))
                    if (promoData.cycles !== '' && !promoData.appliesto.split(',').some(i => i
                            .slice(1) === domain?.ext)) {
                        errors.push(
                            `Berlaku khusus untuk registrasi domain baru: <strong>${promoData.appliesto.split(',').filter(r => r[0] === 'D').map(i => i.slice(1)).join(', ').toUpperCase()}</strong>`
                        )
                        return errors
                    }

                if (promoData.cycles !== '' && !promoData.appliesto.split(',').some(p => {
                        return p.slice(1) === domain?.ext || p.includes('662')
                    })) {
                    errors.push('Kode promo tidak bisa digunakan di paket ini')
                    return errors
                }

                const promoDomainCycle = promoData.cycles.toLowerCase().replaceAll('-', '').split(
                    ',').filter(cycle => cycle.includes('years'))
                const promoHostingCycle = promoData.cycles.toLowerCase().replaceAll('-', '').split(
                    ',').filter(cycle => !cycle.includes('years'))

                let validDomainCycle = promoDomainCycle.some(cycle => {
                    const domainCycleConventionNames = {
                        "1 tahun": "1years",
                        "2 tahun": "2years",
                        "3 tahun": "3years",
                        "4 tahun": "4years",
                        "5 tahun": "5years",
                        "6 tahun": "6years",
                        "7 tahun": "7years",
                        "8 tahun": "8years",
                        "9 tahun": "9years",
                    }

                    return cycle === domainCycleConventionNames[(domain?.billingCycle ??
                        null)]
                })

                let validHostingCycle = promoHostingCycle.some(cycle => cycle === hosting
                    ?.billingCycle)

                if (promoData.cycles !== '' && ((promoDomainCycle.length > 0 && !
                        validDomainCycle) || (promoHostingCycle.length > 0 && !
                        validHostingCycle)))
                    errors.push('Kode promo tidak berlaku untuk billing cycle yang kamu pilih')

                if (validDomainCycle && promoData.requires.split(',').some(r => r[0] === 'D')) {
                    let requirements = promoData.requires.split(',').filter(r => r[0] === 'D').map(
                        r => r.slice(1))
                    let domain = Alpine.store('cart').items.find(i => i.type === 'Domain')

                    if (!domain)
                        errors.push(
                            'Masukkan domain terlebih dahulu untuk menggunakan kode promo ini.',
                            `Berlaku khusus untuk registrasi domain baru: <strong>${requirements.join(', ').toUpperCase()}</strong>`
                        )
                    else {
                        if (!requirements.includes(domain.ext) || domain.billingCycleType !==
                            'register')
                            errors.push(
                                `Berlaku khusus untuk registrasi domain baru: <strong>${requirements.join(', ').toUpperCase()}</strong>`
                            )
                    }
                }

                return errors
            },
        }))
    })
</script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('authenticationForm', () => ({
            showLoginForm: '0' !== '1',
            setAuthType(type) {
                CartValue.userDetail.userStatus = type
                CartValue.userDetail.userData = {}
            }
        }))
        Alpine.data('loginForm', () => ({
            email: '',
            password: '',
            isLoading: false,
            async doLogin() {
                if (this.email === '' || this.password === '')
                    return CustomAlert.open({
                        type: 'error',
                        title: 'Pastikan email atau password kamu terisi dengan benar ya, Sob!',
                        closable: true,
                    })

                this.isLoading = true

                await axios({
                        method: 'post',
                        // url: `https://beli.jagoanhosting.com/api/user/login`,
                        url: `http://dev-whmcs.jagoanhosting.com/api/user/login`,
                        data: {
                            email: this.email,
                            password: this.password
                        },
                        responseType: 'json'
                    }).then((response) => {
                        const result = response.data.result ?? ''

                        if (!result || result !== 'success')
                            return CustomAlert.open({
                                type: 'error',
                                title: 'Oops!',
                                closable: true,
                                descriptions: [response.data.message]
                            })

                        // Set user detail
                        CartValue.userDetail.userStatus = 'login'
                        CartValue.userDetail.userData = {
                            clientid: parseInt(response.data.clientid),
                            email: response.data.email
                        }

                        Alpine.store('userDetails').data.clientid = CartValue.userDetail
                            .userData.clientid
                        Alpine.store('userDetails').data.email = CartValue.userDetail
                            .userData.email
                        Alpine.store('userDetails').data.fullname = response.data.fullname

                        CustomAlert.open({
                            type: 'success',
                            title: 'Login Berhasil',
                            closable: true,
                        })

                        console.log(response.data.fullname);

                        this.email = ''
                        this.password = ''
                    })
                    .catch(error => {
                        CustomAlert.open({
                            type: 'error',
                            title: 'Oops!',
                            closable: true,
                            descriptions: ['Gagal melakukan login.',
                                'Coba periksa lagi email dan password kamu, Sob!'
                            ]
                        })
                    })
                    .finally(() => {
                        this.isLoading = false
                    })
            },
        }))
        Alpine.data('logoutForm', () => ({
            isLoading: false,
            async doLogout() {
                this.isLoading = true

                await axios({
                    method: 'get',
                    url: `https://beli.jagoanhosting.com/api/user/logout`,
                    responseType: 'json'
                }).then(function(response) {
                    const result = response.data.logout ?? ''

                    if (!result)
                        return CustomAlert.open({
                            type: 'error',
                            title: 'Oops!',
                            closable: true,
                            descriptions: ['Gagal melakukan logout, Sob!',
                                'Silakan dicoba lagi.'
                            ]
                        })

                    CustomAlert.open({
                        type: 'success',
                        title: 'Berhasil Logout!',
                        closable: true,
                    })

                    CartValue.userDetail.userStatus = 'login'
                    CartValue.userDetail.userData = {}
                    Alpine.store('userDetails').data = {}
                }).finally(() => {
                    this.isLoading = false
                })
            }
        }))
        Alpine.data('registrationForm', () => ({
            name: '',
            email: '',
            password: '',
            phone: '',
            isLoading: false,
            async doRegister() {
                if (CartValue.userDetail.userStatus === 'register')
                    if (messages = Order.isInvalidRegistrationInput())
                        if (messages.length > 0)
                            return CustomAlert.open({
                                type: 'error',
                                title: 'Masih ada field registrasi yang error, Sob!',
                                closable: true,
                                descriptions: messages
                            })

                this.isLoading = true

                await axios({
                        method: 'post',
                        url: `https://beli.jagoanhosting.com/api/user/register`,
                        data: CartValue.userDetail.userData,
                        responseType: 'json'
                    }).then((response) => {
                        const result = response.data.result ?? ''

                        if (!result || result !== 'success')
                            throw response.data.message;

                        // Set user detail
                        CartValue.userDetail.userStatus = 'login'
                        CartValue.userDetail.userData = {
                            clientid: parseInt(response.data.clientid),
                            email: this.email
                        }

                        Alpine.store('userDetails').data.clientid = CartValue.userDetail
                            .userData.clientid
                        Alpine.store('userDetails').data.email = CartValue.userDetail
                            .userData.email
                        Alpine.store('userDetails').data.fullname = this.name

                        CustomAlert.open({
                            type: 'success',
                            title: 'Registrasi Berhasil',
                            closable: true,
                        })

                        this.email = ''
                        this.password = ''
                        this.name = ''
                        this.phone = ''
                    })
                    .catch(error => {
                        CustomAlert.open({
                            type: 'error',
                            title: 'Gagal melakukan registrasi',
                            closable: true,
                            descriptions: Array.isArray(error) ? error : [error]
                        })
                    })
                    .finally(() => {
                        this.isLoading = false
                    })
            },
            setClientDetail() {
                CartValue.userDetail.userData = {
                    nama: this.name,
                    email: this.email,
                    telepon: Number(this.phone.replaceAll(/[^0-9]/gi, '')),
                    password: this.password
                }
            },
            get isPhoneNumberValid() {
                return this.phone.match(/[0-9\-]/gi)
            }
        }))
    })
</script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('cart', () => ({
            tos: true,
            isLoading: false,
            showDiscountItems: false,
            showAddons: true,
            validateDomain: true,
            validateVps: false,
            get items() {
                return Alpine.store('cart').items
            },
            get subTotal() {
                return {
                    value: () => {
                        const mainCartValue = this.items.filter(i => i.type !== "Promo" && i
                            .billingCycleType !== 'use').reduce((a, b) => a + b.price,
                            0)
                        const addonsCartValue = this.$store.cartAddons?.getItems?.reduce((a,
                            b) => a + (b?.price ?? 0), 0) ?? 0
                        return mainCartValue + addonsCartValue
                    },
                    gimmickValue: () => {
                        const mainCartGimmickValue = this.items.filter(i => i.type !==
                            "Promo" && i.billingCycleType !== 'use').reduce((a, b) =>
                            a + b.gimmickPrice, 0)
                        const addonsCartGimmickValue = this.$store.cartAddons?.getItems
                            ?.reduce((a, b) => a + (b?.gimmickPrice ?? 0), 0) ?? 0
                        return mainCartGimmickValue + addonsCartGimmickValue
                    },
                    renderedValue: () => {
                        return Order.renderIDRValue(this.subTotal.value())
                    },
                }
            },
            get ppn() {
                return {
                    percentageValue: 0.11,
                    value: () => {
                        return this.subTotal.value() * this.ppn.percentageValue
                    },
                    gimmickValue: () => {
                        return this.subTotal.gimmickValue() * this.ppn.percentageValue
                    },
                    renderedValue: () => {
                        return Order.renderIDRValue(this.ppn.value())
                    },
                }
            },
            get finalPrice() {
                return {
                    value: () => {
                        const promoValue = Alpine.store('discountItems').find(i => i
                            .type === 'Promo')?.value ?? 0

                        return this.subTotal.value() + this.ppn.value() - Number(promoValue)
                    },
                    gimmickValue: () => {
                        return this.subTotal.gimmickValue() + this.ppn.gimmickValue()
                    },
                    renderedValue: () => {
                        return Order.renderIDRValue(this.finalPrice.value(), false)
                    },
                    renderedGimmickValue: () => {
                        return Order.renderIDRValue(this.finalPrice.gimmickValue(), false)
                    },
                    showRenderedGimmickValue: () => {
                        return this.finalPrice.gimmickValue() - this.finalPrice.value() > 0
                    },
                }
            },
            skeletonLoading: {
                ['x-html']() {
                    return `<div class="flex justify-between items-center gap-2">
    <p class="h-2 w-32 bg-slate-700 rounded"></p>
    <p class="h-2 w-24 bg-slate-700 rounded"></p>
  </div>
  <div class="flex justify-between items-center gap-2">
    <p class="h-2 w-24 bg-slate-700 rounded"></p>
    <p class="h-2 w-16 bg-slate-700 rounded"></p>
  </div>`
                },
                ['x-show']() {
                    return !this.isReady()
                }
            },
            discountItemsInfo: {
                ['x-html']() {
                    let totalDiscountValue = Order.renderIDRValue(Alpine.store('discountItems')
                        .filter(i => i.type !== 'rawpromo').reduce((a, b) => a + Number(b
                            .value), 0))

                    return Alpine.store('discountItems').length > 0 ?
                        `Total Hemat <strong>${totalDiscountValue}</strong>` :
                        'Dapatkan harga lebih hemat,<br>dengan pilih Billing Cycle tahunan<br>dan gunakan Kode Promo'
                }
            },
            isReady() {
                return this.items.length > 0
            },
            toggleDiscountItems() {
                this.showDiscountItems = !this.showDiscountItems
            },
            async checkout() {
                try {
                    if (this.isLoading)
                        throw 'Pesanan kamu sedang kami proses. Silakan tunggu'

                    this.isLoading = true
                    if (!this.tos)
                        throw 'Pastikan Aturan Layanan udah dicentang ya, Sob!'

                    if (CartValue.items.length === 0)
                        throw 'Keranjang masih kosong, Sob!'

                    if (this.validateDomain) {
                        if (!CartValue.items.some(i => i.type === "Domain"))
                            throw 'Domain belum ditambahkan, Sob!'

                        if (CartValue.items.some(i => i.billingCycleType === "transfer" && i
                                ?.eppCode === ''))
                            throw 'Pastikan kode epp domain sudah terisi, Sob!'
                    }

                    if (this.validateVps) {
                        let vpsErrors = ['Masih ada konfigurasi yang belum diisi, Sob!']
                        let hosting = CartValue.items.find(i => i.type === "Hosting")
                        let configId = hosting.availableCustomFields.find(i => i.name ===
                            "apps_config").id
                        if (!hosting.hostname || hosting.hostname === '' || hosting.hostname
                            .split('.').length < 3 || hosting.hostname.split('.')[2].length <
                            2 || hosting.hostname.match(/[^0-9a-zA-Z-.]/gi))
                            vpsErrors.push('Hostname tidak valid')
                        if (!hosting.rootpw || hosting.rootpwStrength.score <= 0)
                            vpsErrors.push('Password belum sesuai ketentuan')
                        if (hosting.hostname && !hosting.ns1prefix)
                            vpsErrors.push('NS 1 prefix wajib diisi')
                        if (hosting.hostname && !hosting.ns2prefix)
                            vpsErrors.push('NS 2 prefix wajib diisi')
                        if (!hosting.customFields || !hosting?.customFields[configId] || hosting
                            ?.customFields[configId] === '')
                            vpsErrors.push('Operating system belum dipilih')

                        if (vpsErrors.length > 1)
                            throw vpsErrors
                    }

                    const isUserDataHasNotBeenSet = (
                        CartValue.userDetail.userStatus !== 'login' ||
                        Object.keys(CartValue.userDetail.userData).length === 0
                    )

                    if (isUserDataHasNotBeenSet)
                        throw ['Oops!',
                            'Silakan login atau registrasi terlebih dahulu sebelum melanjutkan order, Sob!'
                        ]

                    CustomAlert.open({
                        type: 'loading',
                        title: 'Pesanan kamu sedang diproses, Sob!',
                        descriptions: ['Jangan tutup/reload halaman ini, Sob!',
                            'Kamu sebentar lagi akan diarahkan ke halaman pembayaran'
                        ]
                    })

                    await axios({
                        method: 'post',
                        url: `https://beli.jagoanhosting.com/api/order/662`,
                        data: {
                            userStatus: CartValue.userDetail.userStatus,
                            userData: JSON.stringify({
                                ...CartValue.userDetail.userData,
                                path: window.location.href
                            }),
                            cartData: JSON.stringify(CartValue.items.map(item => {
                                const {
                                    availableConfigOptions,
                                    availableCustomFields,
                                    os,
                                    ...rest
                                } = item
                                return rest
                            })),
                            cartAddons: JSON.stringify(CartAddons.items),
                        },
                        responseType: 'json'
                    }).then(function(response) {
                        if (response.data.code === 0) {
                            console.log(response);
                            return CustomAlert.open({
                                type: 'error',
                                title: 'Terjadi kesalahan saat memproses pesanan kamu, Sob!',
                                descriptions: [response.data.message],
                                closable: true
                            })
                        }

                        CustomAlert.open({
                            type: 'success',
                            title: 'Pesanan kamu sudah kami terima, Sob!',
                            descriptions: [
                                'Jangan tutup/reload halaman ini, Sob!',
                                `Kamu sedang diarahkan ke <a class="underline" href="https://member.jagoanhosting.com/viewinvoice.php?id=${response.data.invoice_id}&amp;new_order=1"><strong>Halaman Pembayaran</strong><a/>`,
                                '<p class="text-xs pt-2 text-gray-solid">*Jika belum diarahkan, silakan klik link halaman pembayaran yang bergaris bawah di atas</p>'
                            ]
                        })

                        // In case SSO is success
                        const url = response.data.redirect_url
                        window.open(url, '_self').focus();
                    })

                } catch (error) {
                    CustomAlert.open({
                        type: 'error',
                        title: Array.isArray(error) ? error[0] : error,
                        closable: true,
                        descriptions: Array.isArray(error) ? error.slice(1) : []
                    })
                } finally {
                    this.isLoading = false
                }
            }
        }))
    })
</script>
<script>
    const CustomAlert = {
        open: (options) => {
            CustomAlert.forceClose()
            // Params
            // {type, title, descriptions, actions, closable, header}
            if (options?.descriptions && options?.descriptions.length > 0) {
                const matchHeader = options?.descriptions?.[0].match(/\[#[^#].+\]/gi)
                const matchTitle = options?.descriptions?.[0].match(/\[##.+\]/gi)
                if (matchHeader) {
                    options.header = options.descriptions[0].match(/\[#[^#].+\]/gi)[0].slice(2, -1).trim()
                    options.descriptions[0] = options.descriptions[0].replaceAll(/\[#[^#].+\]/gi, '').trim()
                }
                if (matchTitle) {
                    options.title = options.descriptions[0].match(/\[##.+\]/gi)[0].slice(3, -1).trim()
                    options.descriptions[0] = options.descriptions[0].replaceAll(/\[##.+\]/gi, '').trim()
                }
            }
            Alpine.store('CustomAlert', options)
            document.querySelector('body').style.overflow = 'hidden'
        },
        close: () => {
            if (!Alpine.store('CustomAlert').closable)
                return

            Alpine.store('CustomAlert', {})
            document.querySelector('body').style.overflow = 'auto'
        },
        forceClose: () => {
            Alpine.store('CustomAlert', {})
            document.querySelector('body').style.overflow = 'auto'
        }
    }

    document.addEventListener('alpine:init', () => {
        Alpine.store('CustomAlert', {})
    })
</script>
<script type="text/javascript">
    window.NREUM || (NREUM = {});
    NREUM.info = {
        "beacon": "bam.nr-data.net",
        "licenseKey": "NRJS-1baa1ee9237ac002545",
        "applicationID": "559023279",
        "transactionName": "YVdWNkoED0tZBxFRDVgddQFMDA5WFxdIXQxCQE1MUQsFXUA=",
        "queueTime": 0,
        "applicationTime": 183,
        "atts": "TRBVQAIeHEU=",
        "errorBeacon": "bam.nr-data.net",
        "agent": ""
    }
</script>
