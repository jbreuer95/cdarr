<nav class="fixed w-full bg-green-600 flex justify-end items-center h-15" >
    <a href="/" class="w-16 md:w-52 flex-shrink-0">
        <img src="/img/logo-full.png" alt="logo" class="hidden md:block h-10 ml-8"/>
        <img src="/img/logo.png" alt="logo" class="md:hidden h-8 ml-5"/>
    </a>
    <div class="flex items-center flex-shrink">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        <input
            class="bg-transparent placeholder-gray-300 w-full focus:outline-none focus:placeholder-transparent border-b focus:border-b-0"
            type="text"
            autocomplete="off"
            placeholder="Search"
            spellcheck="false"
            value=""
        >
    </div>
    <div class="flex flex-1 justify-end mr-6">
        <x-nav-icon route="queue">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
        </x-nav-icon>
        <x-nav-icon route="queue">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        </x-nav-icon>
    </div>
</nav>
