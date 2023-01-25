<div class="p-6 flex flex-col gap-4">
    <h6 class="font-medium">Informasi Akun Pemesan</h6>
    <div class="flex flex-col gap-4" x-show="!$store.userDetails.data.clientid">
        <label @click="showLoginForm = true"
            class="w-full flex items-start gap-y-2 gap-x-4 text-sm cursor-pointer font-bold">
            <input type="radio" class="text-primary" value="login" name="login-option" @change="setAuthType('login')"
                checked>
            <span class="flex flex-col">
                Saya sudah punya akun
            </span>
        </label>
        <div class="w-full flex flex-col md:flex-row items-end gap-4" x-data="loginForm" x-show="showLoginForm">
            <label for="" class="w-full flex flex-col items-start gap-y-2 gap-x-4 text-sm font-bold">
                <span class="flex flex-col">
                    Email
                </span>
                <input type="email" name="email" placeholder="Email"
                    class="flex-1 border border-default rounded text-sm py-3 px-4 w-full" x-model="email">
            </label>
            <label for="" class="w-full flex flex-col items-start gap-y-2 gap-x-4 text-sm font-bold"
                @keyup.enter="doLogin">
                <span class="flex flex-col">
                    Password
                </span>
                <input type="password" name="password" x-model="password" placeholder="Password"
                    class="flex-1 border border-default rounded text-sm py-3 px-4 w-full">
            </label>
            <button
                class="flex items-center justify-center border border-secondary min-w-[100px] py-3 px-4 h-fit flex-none duration-200 text-sm bg-secondary text-white rounded w-full md:w-fit"
                @click="doLogin">
                <span x-show="!isLoading">Login</span>
                <span class="loader" x-show="isLoading" x-cloak></span>
            </button>
        </div>
    </div>
    <div class="flex flex-col md:flex-row gap-4 items-center justify-between p-4 bg-gray-faded rounded"
        x-data="logoutForm" x-show="!!$store.userDetails.data.clientid" x-cloak>
        <div class="flex flex-col gap-1 w-full">
            <h2 class="font-bold" x-text="$store.userDetails.data.fullname"></h2>
            <p x-text="$store.userDetails.data.email"></p>
        </div>
        <button
            class="flex items-center justify-center border border-secondary min-w-[100px] py-3 px-4 h-fit flex-none duration-200 text-sm bg-secondary text-white rounded w-full md:w-fit"
            @click="doLogout">
            <span x-show="!isLoading">Logout</span>
            <span class="loader" x-show="isLoading" x-cloak></span>
        </button>
    </div>
    <div class="border-b border-default" x-show="!$store.userDetails.data.clientid"></div>
    <div class="flex flex-col gap-4" x-show="!$store.userDetails.data.clientid" x-cloak>
        <label @click="showLoginForm = false"
            class="w-full flex items-start gap-y-2 gap-x-4 text-sm cursor-pointer font-bold">
            <input type="radio" class="text-primary" name="login-option" value="register"
                @change="setAuthType('register')">
            <span class="flex flex-col">
                Saya belum punya akun (daftar baru)
            </span>
        </label>
        <div x-data="registrationForm" id="registration-form" x-show="!showLoginForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label for="" class="w-full flex flex-col items-start gap-y-2 gap-x-4 text-sm font-bold">
                    <div class="flex items-center gap-1">
                        <span>Email</span>
                        <span class="italic text-xs text-red-500">*required</span>
                    </div>
                    <input :disabled="isLoading" type="email" name="email" placeholder="Email"
                        class="flex-1 border border-default rounded text-sm py-3 px-4 w-full invalid:border-red-500 peer"
                        x-model="email" @input="setClientDetail" required>
                    <small class="hidden text-red-500 -mt-1 peer-invalid:block">Email tidak
                        valid</small>
                </label>
                <label for="" class="w-full flex flex-col items-start gap-y-2 gap-x-4 text-sm font-bold">
                    <div class="flex items-center gap-1">
                        <span>Telepon (Nomor WhatsApp)</span>
                        <span class="italic text-xs text-red-500">*required</span>
                    </div>
                    <div class="flex items-center w-full border border-default rounded overflow-hidden focus-within:border-primary invalid:border-red-500"
                        :class="isPhoneNumberValid ? '' : 'border-red-500'">
                        <div class="py-3 px-4 bg-gray-faded">+62</div>
                        <input :disabled="isLoading" type="text" name="password" x-mask="999-9999-99999"
                            placeholder="812-3456-7890" class="flex-1 text-sm py-3 px-4 w-full border-0" x-model="phone"
                            @input="setClientDetail" pattern="[0-9\-]+" required>
                    </div>
                </label>
                <label for="" class="w-full flex flex-col items-start gap-y-2 gap-x-4 text-sm font-bold ">
                    <div class="flex items-center gap-1">
                        <span>Nama</span>
                        <span class="italic text-xs text-red-500">*required</span>
                    </div>
                    <input :disabled="isLoading" type="text" name="name" placeholder="Nama"
                        class="flex-1 border border-default rounded text-sm py-3 px-4 w-full invalid:border-red-500 peer"
                        x-model="name" @input="setClientDetail" pattern="[a-zA-Z\-\s]+" required>
                    <small class="hidden text-red-500 -mt-1 peer-invalid:block">Nama hanya
                        bisa diisi dengan karakter saja</small>
                </label>
                <label for="" class="w-full flex flex-col items-start gap-y-2 gap-x-4 text-sm font-bold">
                    <div class="flex items-center gap-1">
                        <span>Password</span>
                        <span class="italic text-xs text-red-500">*required</span>
                    </div>
                    <input :disabled="isLoading" type="password" name="password" placeholder="Password"
                        class="flex-none border border-default rounded text-sm py-3 px-4 w-full invalid:border-red-500"
                        x-model="password" @input="setClientDetail" required>
                </label>
            </div>
            <div class="w-full flex justify-end mt-4">
                <button
                    class="flex items-center justify-center border border-secondary min-w-[100px] py-3 px-4 h-fit flex-none duration-200 text-sm bg-secondary text-white rounded w-full"
                    :disabled="isLoading" @click="doRegister">
                    <span x-show="!isLoading">Register</span>
                    <span class="loader" x-show="isLoading" x-cloak></span>
                </button>
            </div>
        </div>
    </div>
</div>
