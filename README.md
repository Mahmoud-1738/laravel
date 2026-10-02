# Eindopdracht Laravel – Blog

Een blog gemaakt met Laravel 12. Er is een overzichtspagina met alle posts en een detailpagina voor elke post.
De data komt uit de database (Eloquent models) en wordt getoond met Blade.

De frontend is de **Laravel From Scratch HTML/CSS template** (`index.html` en `post.html`), omgezet naar Blade components.
De plaatjes uit de template staan in `public/images`.

Extra functionaliteiten:

1. Zoekfunctionaliteit
2. Filtering (op categorie)
3. Pagination
4. Comments bij een post
5. Inlogsysteem + Administrator gedeelte (CMS om posts toe te voegen, te bewerken en te verwijderen)
6. File upload (afbeelding bij een post)
7. Nieuwsbrief (inschrijven in de footer, e-mailadressen worden opgeslagen in de database)

## CMS / inloggen

Ga naar http://localhost:8000/login en log in met het admin account uit de seeder:

- E-mail: `admin@example.com`
php artisan serve- Wachtwoord: `password`

Daarna kom je in het CMS (`/admin/posts`). Daar kan je posts toevoegen, bewerken en verwijderen.

---

## Installatie

Nodig: PHP 8.2+, Composer en MySQL (bijvoorbeeld via XAMPP).

1. Start Apache en MySQL in XAMPP.
2. Maak in phpMyAdmin een database aan met de naam `eindopdracht`.
3. Draai deze commando's:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Ga daarna naar http://localhost:8000

`php artisan storage:link` is nodig voor de geüploade afbeeldingen: het maakt een link van `public/storage` naar `storage/app/public`.

De database instellingen staan in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eindopdracht
DB_USERNAME=root
DB_PASSWORD=
```

`php artisan migrate --seed` maakt alle tabellen aan en vult ze met testdata (10 posts, 3 categorieën en reacties).

## Structuur

| Onderdeel | Bestand |
|---|---|
| Routes | `routes/web.php` |
| Controllers | `app/Http/Controllers/` – `PostController`, `CommentController`, `SessionController`, `AdminPostController`, `NewsletterController` |
| Models | `app/Models/Post.php`, `Category.php`, `Comment.php`, `Subscriber.php` |
| Migrations | `database/migrations/` |
| Testdata | `database/seeders/DatabaseSeeder.php` + factories |
| Layout (nav + nieuwsbrief footer) | `resources/views/components/layout.blade.php` |
| Overzicht | `resources/views/posts/index.blade.php` |
| Detail + reacties | `resources/views/posts/show.blade.php` |
| Grid met posts | `resources/views/components/posts-grid.blade.php` |
| Grote post (bovenaan) | `resources/views/components/post-featured-card.blade.php` |
| Post kaartje | `resources/views/components/post-card.blade.php` |
| Categorie label | `resources/views/components/category-button.blade.php` |
| Paginatie | `resources/views/pagination.blade.php` |
| Login | `resources/views/sessions/create.blade.php` |
| CMS | `resources/views/admin/posts/` |

Database:

- `categories` (id, name, slug)
- `posts` (id, user_id, category_id, slug, thumbnail, title, excerpt, body, published_at)
- `comments` (id, post_id, name, body)
- `subscribers` (id, email)

Relaties: een Post hoort bij een Category en een User (author), en een Post heeft meerdere Comments.

---

## Handleiding 1: Zoekfunctionaliteit

Met een zoekveld kan je zoeken in de titel en de tekst van de posts.

**Stap 1 – zoekformulier in de view** (`posts/index.blade.php`)

Een gewoon GET formulier, zodat de zoekterm in de url komt (`/?search=laravel`).

```blade
<form method="GET" action="/">
    <input type="text" name="search" placeholder="Zoek een post..." value="{{ request('search') }}">
    <button type="submit">Zoeken</button>
</form>
```

`value="{{ request('search') }}"` zorgt dat de zoekterm blijft staan na het zoeken.

**Stap 2 – query scope in het model** (`app/Models/Post.php`)

```php
public function scopeFilter($query, array $filters)
{
    if (! empty($filters['search'])) {
        $search = $filters['search'];

        $query->where(function ($query) use ($search) {
            $query->where('title', 'like', '%' . $search . '%')
                ->orWhere('body', 'like', '%' . $search . '%');
        });
    }
}
```

De `where(function ...)` is nodig zodat de `orWhere` niet de andere filters (categorie) kapot maakt.

**Stap 3 – gebruiken in de controller** (`PostController@index`)

```php
$posts = Post::latest('published_at')
    ->filter(request(['search', 'category']))
    ->get();
```

**Veilig?** Ja. Eloquent gebruikt prepared statements, dus SQL injection (bijv. `' OR 1=1 --`) werkt niet.
De zoekterm wordt in de view getoond met `{{ }}` en dat escaped HTML, dus `<script>` wordt niet uitgevoerd.

---

## Handleiding 2: Filtering op categorie

Je kan de posts filteren op categorie met een dropdown of door op het label van een post te klikken (`/?category=php`).

**Stap 1 – categories tabel en relatie**

Migration voor categories met `name` en `slug`, en in de posts tabel:

```php
$table->foreignId('category_id')->constrained()->cascadeOnDelete();
```

In `Post.php`:

```php
public function category()
{
    return $this->belongsTo(Category::class);
}
```

In `Category.php`:

```php
public function posts()
{
    return $this->hasMany(Post::class);
}
```

**Stap 2 – filter toevoegen aan de scope** (`Post.php`, in `scopeFilter`)

```php
if (! empty($filters['category'])) {
    $category = $filters['category'];

    $query->whereHas('category', function ($query) use ($category) {
        $query->where('slug', $category);
    });
}
```

**Stap 3 – categorieën meegeven aan de view** (`PostController@index`)

```php
return view('posts.index', [
    'posts' => $posts,
    'categories' => Category::orderBy('name')->get(),
]);
```

**Stap 4 – dropdown in hetzelfde formulier als zoeken**

```blade
<select name="category" onchange="this.form.submit()">
    <option value="">Alle categorieën</option>
    @foreach ($categories as $category)
        <option value="{{ $category->slug }}" @selected(request('category') == $category->slug)>
            {{ $category->name }}
        </option>
    @endforeach
</select>
```

Omdat de select en het zoekveld in hetzelfde formulier zitten kan je ze combineren: `/?category=php&search=veilig`.

---

## Handleiding 3: Pagination

Er staan 6 posts per pagina, met onderaan knoppen naar de volgende pagina.

**Stap 1 – `paginate()` in plaats van `get()`** (`PostController@index`)

```php
$posts = Post::latest('published_at')
    ->filter(request(['search', 'category']))
    ->paginate(6)
    ->withQueryString();
```

`withQueryString()` is belangrijk: anders ben je je zoekterm en categorie kwijt als je naar pagina 2 gaat.

**Stap 2 – links tonen in de view**

```blade
{{ $posts->links('pagination') }}
```

Ik wilde knoppen in dezelfde stijl als de "Read More" knoppen uit de template, daarom heb ik een eigen view gemaakt:
`resources/views/pagination.blade.php`. Daarin krijg je `$paginator` en kan je deze functies gebruiken:

```blade
@if ($paginator->hasPages())
    @if ($paginator->onFirstPage())
        <span>&larr; vorige</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}">&larr; vorige</a>
    @endif

    <span>pagina {{ $paginator->currentPage() }} van {{ $paginator->lastPage() }}</span>

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}">volgende &rarr;</a>
    @endif
@endif
```

**Stap 3 – geen resultaten**

```blade
@if ($posts->count())
    ...
@else
    <p>Er zijn geen posts gevonden.</p>
@endif
```

---

## Handleiding 4: Comments bij een post

Op de detailpagina kan iedereen een reactie plaatsen met naam en tekst.

**Stap 1 – migration en model**

```bash
php artisan make:model Comment -mf
```

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->text('body');
    $table->timestamps();
});
```

In `Comment.php` `protected $fillable = ['post_id', 'name', 'body'];` en in `Post.php`:

```php
public function comments()
{
    return $this->hasMany(Comment::class);
}
```

**Stap 2 – route** (`routes/web.php`)

```php
Route::post('posts/{post:slug}/comments', [CommentController::class, 'store'])->middleware('throttle:5,1');
```

`throttle:5,1` = maximaal 5 reacties per minuut, tegen spam.

**Stap 3 – controller** (`CommentController@store`)

```php
public function store(Request $request, Post $post)
{
    $attributes = $request->validate([
        'name' => 'required|max:50',
        'body' => 'required|min:3|max:1000',
    ]);

    $post->comments()->create($attributes);

    return redirect('/posts/' . $post->slug . '#comments')->with('success', 'Bedankt! Je reactie is geplaatst.');
}
```

In de echte code staan ook Nederlandse foutmeldingen als tweede parameter van `validate()`.

**Stap 4 – formulier en lijst in de view** (`posts/show.blade.php`)

```blade
<form method="POST" action="/posts/{{ $post->slug }}/comments">
    @csrf

    <input type="text" name="name" value="{{ old('name') }}">
    @error('name')
        <p>{{ $message }}</p>
    @enderror

    <textarea name="body">{{ old('body') }}</textarea>
    @error('body')
        <p>{{ $message }}</p>
    @enderror

    <button type="submit">Plaatsen</button>
</form>

@foreach ($comments as $comment)
    <p>{{ $comment->name }}: {{ $comment->body }}</p>
@endforeach
```

- `@csrf` is verplicht, anders krijg je een 419 error (bescherming tegen CSRF).
- `old()` zorgt dat je ingevulde tekst blijft staan als er een fout is.
- `{{ }}` escaped de reactie, dus als iemand `<script>` typt wordt dat gewoon als tekst getoond.

---

## Handleiding 5: Inlogsysteem + Administrator gedeelte (CMS)

Alleen ingelogde gebruikers kunnen posts toevoegen, bewerken en verwijderen.

**Stap 1 – admin gebruiker in de seeder** (`DatabaseSeeder.php`)

```php
$user = User::factory()->create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => 'password',
]);
```

Het wachtwoord wordt automatisch gehasht door de `'password' => 'hashed'` cast in het User model.

**Stap 2 – routes** (`routes/web.php`)

```php
Route::get('login', [SessionController::class, 'create'])->middleware('guest')->name('login');
Route::post('login', [SessionController::class, 'store'])->middleware(['guest', 'throttle:5,1']);
Route::post('logout', [SessionController::class, 'destroy'])->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('admin/posts', [AdminPostController::class, 'index']);
    Route::get('admin/posts/create', [AdminPostController::class, 'create']);
    Route::post('admin/posts', [AdminPostController::class, 'store']);
    Route::get('admin/posts/{post}/edit', [AdminPostController::class, 'edit']);
    Route::patch('admin/posts/{post}', [AdminPostController::class, 'update']);
    Route::delete('admin/posts/{post}', [AdminPostController::class, 'destroy']);
});
```

- `auth` middleware: ben je niet ingelogd, dan word je naar `/login` gestuurd (daarom heet die route `login`).
- `guest` middleware: als je al ingelogd bent kan je niet nog een keer naar de login pagina.
- `throttle:5,1` op login: max 5 pogingen per minuut, tegen brute force.

**Stap 3 – inloggen** (`SessionController@store`)

```php
$credentials = $request->validate([
    'email' => 'required|email',
    'password' => 'required',
]);

if (! Auth::attempt($credentials)) {
    return back()
        ->withInput($request->only('email'))
        ->withErrors(['email' => 'E-mailadres of wachtwoord is fout.']);
}

$request->session()->regenerate();

return redirect('/admin/posts');
```

`Auth::attempt()` checkt het wachtwoord met de hash in de database. `session()->regenerate()` maakt een nieuwe sessie aan (tegen session fixation).
Uitloggen gaat met een POST formulier (met `@csrf`), niet met een gewone link.

**Stap 4 – CRUD controller** (`AdminPostController`)

- `index` – tabel met alle posts
- `create` / `store` – nieuwe post toevoegen
- `edit` / `update` – post aanpassen
- `destroy` – post verwijderen

De validatie staat in een functie zodat store en update dezelfde regels gebruiken:

```php
protected function validatePost(Request $request, ?Post $post = null)
{
    return $request->validate([
        'title' => 'required|max:255',
        'slug' => ['required', 'max:255', 'alpha_dash', Rule::unique('posts', 'slug')->ignore($post?->id)],
        'excerpt' => 'required|max:500',
        'body' => 'required',
        'category_id' => ['required', Rule::exists('categories', 'id')],
    ]);
}
```

- `Rule::unique(...)->ignore($post?->id)`: de slug moet uniek zijn, behalve bij de post die je aan het bewerken bent.
- `Rule::exists('categories', 'id')`: je kan geen categorie kiezen die niet bestaat.
- `user_id` en `published_at` komen niet uit het formulier maar worden in de controller gezet (`auth()->id()` en `now()`), zodat niemand dat kan vervalsen.

**Stap 5 – views**

- `resources/views/sessions/create.blade.php` – login formulier
- `resources/views/admin/posts/index.blade.php` – tabel met Bewerken en Verwijderen
- `resources/views/admin/posts/form.blade.php` – de velden, wordt met `@include` gebruikt in `create` en `edit`

Bewerken en verwijderen gebruiken `@method('PATCH')` en `@method('DELETE')`, omdat een HTML formulier alleen GET en POST kan.
In het formulier wordt `old('title', $post->title)` gebruikt: bij een fout blijft je invoer staan, en bij bewerken staat de huidige waarde erin.

In de layout wordt met `@auth` / `@else` gekozen of je de link "Inloggen" of "CMS" + "Uitloggen" ziet.

---

## Handleiding 6: File upload (afbeelding bij een post)

In het CMS kan je een afbeelding uploaden bij een post. Zonder upload wordt een van de plaatjes uit de template gebruikt.

**Stap 1 – kolom toevoegen met een nieuwe migration**

```bash
php artisan make:migration add_thumbnail_to_posts_table --table=posts
```

```php
Schema::table('posts', function (Blueprint $table) {
    $table->string('thumbnail')->nullable()->after('slug');
});
```

`nullable()` omdat een afbeelding niet verplicht is. Vergeet niet `'thumbnail'` aan `$fillable` in `Post.php` toe te voegen.

**Stap 2 – storage link maken**

```bash
php artisan storage:link
```

Bestanden worden opgeslagen in `storage/app/public`. Door de link zijn ze bereikbaar via `/storage/...`.

**Stap 3 – formulier** (`admin/posts/create.blade.php` en `form.blade.php`)

```blade
<form method="POST" action="/admin/posts" enctype="multipart/form-data">
    ...
    <input type="file" name="thumbnail" id="thumbnail" accept="image/*">
</form>
```

Zonder `enctype="multipart/form-data"` wordt het bestand niet meegestuurd!

**Stap 4 – valideren en opslaan** (`AdminPostController@validatePost`)

```php
'thumbnail' => 'nullable|image|max:2048',
```

```php
if ($request->hasFile('thumbnail')) {
    $attributes['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
} else {
    unset($attributes['thumbnail']);
}
```

- `image`: alleen jpg, png, gif, bmp en webp. Een `.php` bestand of een SVG wordt geweigerd.
- `max:2048`: maximaal 2 MB (in kilobytes).
- `store()` geeft het bestand een random naam, zodat niemand een bestand kan overschrijven.
- De `unset` zorgt dat bij bewerken zonder nieuwe afbeelding de oude afbeelding blijft staan.

**Stap 5 – tonen** (`Post.php`)

```php
public function thumbnailUrl()
{
    if ($this->thumbnail) {
        return asset('storage/' . $this->thumbnail);
    }

    return '/images/illustration-' . ($this->id % 5 + 1) . '.png';
}
```

In de views: `<img src="{{ $post->thumbnailUrl() }}">`.

---

## Handleiding 7: Nieuwsbrief

Onderaan elke pagina (de footer uit de template) kan je je inschrijven met je e-mailadres.
De adressen worden opgeslagen in de tabel `subscribers` (nog niet gekoppeld aan Mailchimp).

**Stap 1 – model en migration**

```bash
php artisan make:model Subscriber -m
```

```php
Schema::create('subscribers', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->timestamps();
});
```

**Stap 2 – route**

```php
Route::post('newsletter', [NewsletterController::class, 'store'])->middleware('throttle:5,1');
```

**Stap 3 – controller** (`NewsletterController@store`)

```php
$attributes = $request->validateWithBag('newsletter', [
    'email' => 'required|email|max:255|unique:subscribers,email',
]);

Subscriber::create($attributes);

return redirect(url()->previous() . '#newsletter')->with('newsletter', 'Bedankt! Je bent ingeschreven voor de nieuwsbrief.');
```

`validateWithBag('newsletter', ...)` zet de fouten in een aparte "error bag". Anders zou een fout bij de nieuwsbrief
ook onder het e-mail veld van de login pagina verschijnen (die heet ook `email`).

**Stap 4 – formulier in de footer** (`components/layout.blade.php`)

```blade
<form method="POST" action="/newsletter">
    @csrf
    <input id="newsletter-email" name="email" type="text" placeholder="Je e-mailadres">
    <button type="submit">Inschrijven</button>
</form>

@error('email', 'newsletter')
    <p>{{ $message }}</p>
@enderror

@if (session('newsletter'))
    <p>{{ session('newsletter') }}</p>
@endif
```

---

## Veiligheid

- **SQL injection**: alle queries gaan via Eloquent (prepared statements).
- **XSS**: alles wordt getoond met `{{ }}`. De tekst van een post wordt per alinea getoond met `{!! nl2br(e($paragraph)) !!}`, `e()` escaped eerst de HTML en daarna worden alleen de enters omgezet naar `<br>`.
- **CSRF**: `@csrf` in elk formulier.
- **Validatie**: alle formulieren worden gevalideerd (verplichte velden, max lengte, geldig e-mailadres).
- **Spam**: throttle op de comment, nieuwsbrief en login routes.
- **Niet bestaande post**: `/posts/bestaat-niet` geeft een 404 (route model binding op slug).
- **CMS**: alleen bereikbaar als je ingelogd bent (`auth` middleware), wachtwoorden zijn gehasht, max 5 login pogingen per minuut.
- **Upload**: alleen afbeeldingen tot 2 MB, met een random bestandsnaam.

## Testen

Automatische tests staan in `tests/Feature/BlogTest.php`, `AdminTest.php` en `NewsletterTest.php`:

```bash
php artisan test
```

Handmatig getest:

| Test | Invoer | Verwacht resultaat | OK |
|---|---|---|---|
| Overzicht | `/` | 6 posts + paginatie | ✅ |
| Detailpagina | klik op een post | hele post + reacties | ✅ |
| Niet bestaande post | `/posts/abc` | 404 pagina | ✅ |
| Zoeken | `laravel` | alleen posts met laravel | ✅ |
| Zoeken zonder resultaat | `xyzxyz` | "Er zijn geen posts gevonden" | ✅ |
| Zoeken SQL injection | `' OR 1=1 --` | geen error, geen resultaten | ✅ |
| Zoeken HTML | `<script>alert(1)</script>` | wordt als tekst getoond | ✅ |
| Zoeken leeg | (leeg) | alle posts | ✅ |
| Filter categorie | PHP | alleen PHP posts | ✅ |
| Filter + zoeken | PHP + `veilig` | 1 post | ✅ |
| Onbekende categorie | `/?category=abc` | geen posts, geen error | ✅ |
| Pagina 2 met filter | `/?category=laravel&page=2` | filter blijft bewaard | ✅ |
| Rare pagina | `/?page=999` | geen posts, geen error | ✅ |
| Reactie leeg | naam en tekst leeg | foutmeldingen | ✅ |
| Reactie te lang | naam > 50 tekens | foutmelding | ✅ |
| Reactie met HTML | `<b>hoi</b>` | als tekst getoond | ✅ |
| Reactie spam | 6x snel achter elkaar | 429 Too Many Requests | ✅ |
| CMS zonder inloggen | `/admin/posts` | doorgestuurd naar `/login` | ✅ |
| Inloggen fout wachtwoord | goed e-mail, fout wachtwoord | foutmelding, niet ingelogd | ✅ |
| Inloggen goed | admin account | naar het CMS | ✅ |
| Post toevoegen | alle velden ingevuld | post staat op de site | ✅ |
| Post toevoegen leeg | lege velden | foutmeldingen, invoer blijft staan | ✅ |
| Slug bestaat al | slug van andere post | "Deze slug bestaat al" | ✅ |
| Slug met spaties/tekens | `mijn post!` | foutmelding | ✅ |
| Categorie die niet bestaat | id aangepast in inspector | foutmelding | ✅ |
| Post bewerken | titel aanpassen | nieuwe titel op de site | ✅ |
| Post verwijderen | klik Verwijderen + OK | post is weg | ✅ |
| Uitloggen | klik Uitloggen | terug naar home, CMS niet meer bereikbaar | ✅ |
| Afbeelding uploaden | jpg van 500 KB | afbeelding bij de post | ✅ |
| Post zonder afbeelding | niks gekozen | plaatje uit de template | ✅ |
| Geen afbeelding uploaden | `hack.php` | "Het bestand moet een afbeelding zijn" | ✅ |
| Te grote afbeelding | jpg van 3 MB | "maximaal 2 MB" | ✅ |
| Bewerken zonder nieuwe afbeelding | alleen titel aanpassen | oude afbeelding blijft | ✅ |
| Nieuwsbrief | geldig e-mailadres | "Bedankt! Je bent ingeschreven" | ✅ |
| Nieuwsbrief ongeldig | `geen-email` of leeg | foutmelding | ✅ |
| Nieuwsbrief dubbel | zelfde adres 2x | "Je bent al ingeschreven" | ✅ |
