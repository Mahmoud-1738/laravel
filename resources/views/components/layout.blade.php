<!doctype html>
<html lang="nl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Laravel From Scratch Blog' }}</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body style="font-family: Open Sans, sans-serif">
    <section class="px-6 py-8">
        <nav class="md:flex md:justify-between md:items-center">
            <div>
                <a href="/">
                    <img src="/images/logo.svg" alt="Laracasts Logo" width="165" height="16">
                </a>
            </div>

            <div class="mt-8 md:mt-0 flex items-center">
                <a href="/" class="text-xs font-bold uppercase">Home</a>

                @auth
                    <a href="/admin/posts" class="text-xs font-bold uppercase ml-6">CMS</a>

                    <form method="POST" action="/logout" class="ml-6">
                        @csrf
                        <button type="submit" class="text-xs font-bold uppercase">Uitloggen</button>
                    </form>
                @else
                    <a href="/login" class="text-xs font-bold uppercase ml-6">Inloggen</a>
                @endauth

                <a href="#newsletter"
                    class="transition-colors duration-300 bg-blue-500 hover:bg-blue-600 ml-6 rounded-full text-xs font-semibold text-white uppercase py-3 px-5">
                    Schrijf je in
                </a>
            </div>
        </nav>

        {{ $slot }}

        <footer id="newsletter" class="bg-gray-100 border border-black border-opacity-5 rounded-xl text-center py-16 px-10 mt-16">
            <img src="/images/lary-newsletter-icon.svg" alt="" class="mx-auto -mb-6" style="width: 145px;">
            <h5 class="text-3xl">Blijf op de hoogte van nieuwe posts</h5>
            <p class="text-sm mt-3">Geen spam, beloofd. En geen bugs.</p>

            <div class="mt-10">
                <div class="relative inline-block mx-auto lg:bg-gray-200 rounded-full">
                    <form method="POST" action="/newsletter" class="lg:flex text-sm">
                        @csrf

                        <div class="lg:py-3 lg:px-5 flex items-center">
                            <label for="newsletter-email" class="hidden lg:inline-block">
                                <img src="/images/mailbox-icon.svg" alt="mailbox letter">
                            </label>

                            <input id="newsletter-email" name="email" type="text" placeholder="Je e-mailadres"
                                class="lg:bg-transparent py-2 lg:py-0 pl-4 focus-within:outline-none">
                        </div>

                        <button type="submit"
                            class="transition-colors duration-300 bg-blue-500 hover:bg-blue-600 mt-4 lg:mt-0 lg:ml-3 rounded-full text-xs font-semibold text-white uppercase py-3 px-8">
                            Inschrijven
                        </button>
                    </form>
                </div>

                @error('email', 'newsletter')
                    <p class="text-red-500 text-xs mt-3">{{ $message }}</p>
                @enderror

                @if (session('newsletter'))
                    <p class="text-green-600 text-sm mt-3">{{ session('newsletter') }}</p>
                @endif
            </div>
        </footer>
    </section>
</body>

</html>
