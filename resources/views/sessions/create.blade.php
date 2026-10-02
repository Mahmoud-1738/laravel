<x-layout title="Inloggen">
    <main class="max-w-lg mx-auto mt-10 lg:mt-20">
        <div class="bg-gray-100 border border-black border-opacity-5 rounded-xl p-6 lg:p-10">
            <h1 class="text-center font-bold text-xl">Inloggen</h1>
            <p class="text-center text-sm text-gray-500 mt-2">Alleen voor de beheerder van deze blog.</p>

            <form method="POST" action="/login" class="mt-10">
                @csrf

                <div class="mb-6">
                    <label for="email" class="block mb-2 uppercase font-bold text-xs text-gray-700">E-mailadres</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="border border-gray-400 p-2 w-full rounded">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="password" class="block mb-2 uppercase font-bold text-xs text-gray-700">Wachtwoord</label>
                    <input type="password" name="password" id="password" required
                        class="border border-gray-400 p-2 w-full rounded">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="transition-colors duration-300 bg-blue-500 hover:bg-blue-600 rounded-full text-xs font-semibold text-white uppercase py-3 px-10">
                    Inloggen
                </button>
            </form>
        </div>
    </main>
</x-layout>
