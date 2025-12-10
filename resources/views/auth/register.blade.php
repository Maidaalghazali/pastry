<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block font-medium text-sm text-gray-700">Nama</label>
            <input id="name"
                   class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                   type="text"
                   name="name"
                   value="{{ old('name') }}"
                   required
                   autofocus
                   autocomplete="name">
            @error('name')
                <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
            <input id="email"
                   class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autocomplete="username">
            @error('email')
                <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block font-medium text-sm text-gray-700">Password</label>
            <input id="password"
                   class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                   type="password"
                   name="password"
                   required
                   autocomplete="new-password">
            @error('password')
                <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Konfirmasi Password</label>
            <input id="password_confirmation"
                   class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                   type="password"
                   name="password_confirmation"
                   required
                   autocomplete="new-password">
            @error('password_confirmation')
                <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                Sudah punya akun?
            </a>

            <button type="submit"
                    class="ml-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Daftar
            </button>
        </div>
    </form>
</x-guest-layout>
