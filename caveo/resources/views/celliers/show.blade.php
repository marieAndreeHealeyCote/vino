@php use Illuminate\Support\Str; @endphp
@extends('layouts.main')

@section('title', $cellier->nom)

@section('fleche')
<!-- Flèche de retour qui revient vers le cellier ou le catalogue selon la source -->
<a href="{{ route('celliers.index') }}" class="text-white text-2xl leading-none" aria-label="Retour">
    <img src="{{ asset('images/fleches/gauche-blanc.svg') }}" alt="Flèche de retour" class="w-10 h-10">
</a>
@endsection

@section('content')
<script type="module" src="{{ asset('js/message-flash-auto.js') }}"></script>
<script type="module" src="{{ asset('js/confirmation-suppression.js') }}"></script>

<section class="px-4 py-5 pb-48 max-w-5xl mx-auto font-roboto">

    <div class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-3xl text-[#7A1E2E]" style="font-family: 'Crimson Text', serif;">
                {{ $cellier->nom }}
            </h1>

            <div class="flex gap-3 flex-wrap">

                <a href="{{ route('catalogue.index') }}"
                    class="border border-gray-300 px-4 py-3 rounded font-medium">
                    Catalogue
                </a>

                <a href="{{ route('celliers.bouteilles.create', $cellier) }}"
                    class="bg-[#A83248] text-white px-4 py-3 rounded font-semibold">
                    Ajouter bouteille non listée
                </a>

            </div>
        </div>

        <div class="mt-3 text-sm text-gray-700 space-y-1">
            @if($cellier->emplacement)
            <p><strong>Emplacement :</strong> {{ $cellier->emplacement }}</p>
            @endif

            @if($cellier->description)
            <p><strong>Description :</strong> {{ $cellier->description }}</p>
            @endif
        </div>
    </div>

    <x-alerts />

    <div class="mt-4 mb-6 text-xs text-gray-500 italic space-y-1">
        <p>* Mettre 0 indique que la bouteille a été bue.</p>
        <p>* Seules les bouteilles non listées sont modifiables.</p>
    </div>

    <div class="space-y-4 pb-20">

        <div class="space-y-4 pb-20">
            @forelse($cellier->inventaires as $inventaire)
            <div class="flex gap-6 mb-6 font-roboto border p-4 rounded bg-white">

                <div class="w-[90px] flex justify-center items-center shrink-0">
                    @php
                    $image = $inventaire->bouteille->image ?? null;
                    @endphp

                    <img
                        src="{{ $image
            ? (Str::startsWith($image, ['http://', 'https://'])
                ? $image
                : asset('storage/' . $image))
            : asset('images/bouteille-vide.png') }}"
                        alt="{{ $inventaire->bouteille->nom ?? 'Bouteille' }}"
                        class="w-auto h-[135px]">
                </div>

                <div class="flex flex-col justify-between flex-1 min-w-0">
                    <div>
                        <div class="flex justify-between items-start gap-3">
                            <h2 class="font-semibold text-lg break-words">
                                {{ $inventaire->bouteille->nom ?? 'N/A' }}
                            </h2>

                            @if($inventaire->quantite == 0)
                            <span class="bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full shrink-0">
                                Bue
                            </span>
                            @endif
                        </div>

                        <div class="flex items-center text-sm text-gray-600 space-x-2 flex-wrap">
                            @if(!empty($inventaire->bouteille->pays))
                            <p>{{ $inventaire->bouteille->pays }}</p>
                            @endif

                            @if(!empty($inventaire->bouteille->pays) && !empty($inventaire->bouteille->format))
                            <span>|</span>
                            @endif

                            @if(!empty($inventaire->bouteille->format))
                            <p>{{ $inventaire->bouteille->format }} ml</p>
                            @endif

                            @if((!empty($inventaire->bouteille->pays) || !empty($inventaire->bouteille->format)) && !empty($inventaire->bouteille->type))
                            <span>|</span>
                            @endif

                            @if(!empty($inventaire->bouteille->type))
                            <p>{{ $inventaire->bouteille->type }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex gap-4 items-center justify-between w-full">
                        <div class="flex items-center gap-2">

                            @if($inventaire->bouteille)
                            <a href="{{ route('bouteilles.show', $inventaire->bouteille->id) }}?source=cellier&inventaire={{ $inventaire->id }}"
                                class="px-2 py-2 border border-gray-300 rounded hover:bg-gray-100 flex items-center justify-center"
                                title="Détail de la bouteille"
                                aria-label="Voir les détails">
                                <img src="{{ asset('images/symbole/info.svg') }}" alt="" aria-hidden="true" class="w-6 h-6">
                            </a>
                            @endif

                            <!-- Modifier -->
                            @if($inventaire->bouteille && !$inventaire->bouteille->est_saq)
                            <a href="{{ route('celliers.bouteilles.edit', [$cellier, $inventaire->bouteille]) }}"
                                class="px-2 py-2 border border-gray-300 rounded hover:bg-gray-100 flex items-center justify-center"
                                title="Modifier la bouteille"
                                aria-label="Modifier la bouteille">
                                <img src="{{ asset('images/icons/crayon.svg') }}" alt="" aria-hidden="true" class="w-6 h-6">
                            </a>
                            @endif
                        </div>
                        <!-- Supprimer -->
                        @if($inventaire->quantite == 0)
                        <form method="POST" action="{{ route('inventaires.destroy', $inventaire) }}" class="inline-flex">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="bouton-supprimer px-2 py-2 border border-gray-300 text-gray-600 rounded hover:bg-gray-100 flex items-center justify-center"
                                data-confirm="Supprimer cette bouteille ?" aria-label="Supprimer la bouteille">
                                <img src="{{ asset('images/icons/poubelle.svg') }}" alt="" aria-hidden="true"
                                    class="w-6 h-6">
                            </button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('inventaires.updateQuantite', $inventaire) }}">
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="quantite" value="{{ max(0, $inventaire->quantite - 1) }}">

                            <button type="submit" class="w-full flex items-center justify-center"
                                aria-label="Diminuer la quantité" title="Diminuer la quantité">
                                <img src="{{ asset('images/icons/cercle-moins.svg') }}" alt="moins" aria-hidden="true"
                                    class="w-10 h-10">
                            </button>
                        </form>
                        @endif

                        <div class="text-center">
                            <span class="text-2xl font-semibold">
                                {{ $inventaire->quantite }}
                            </span>
                        </div>

                        <form method="POST" action="{{ route('inventaires.updateQuantite', $inventaire) }}">
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="quantite" value="{{ min(999, $inventaire->quantite + 1) }}">

                            <button type="submit" class="w-full flex items-center justify-center"
                                aria-label="Augmenter la quantité" title="Augmenter la quantité">
                                <img src="{{ asset('images/icons/cercle-plus.svg') }}" alt="plus" aria-hidden="true"
                                    class="w-10 h-10">
                            </button>
                        </form>
                    </div>

                    <div class="mt-1">
                        @if($inventaire->quantite == 0)
                        <p class="text-xs text-red-500 mt-1">
                            Cette bouteille a été bue.
                        </p>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div
                class="mt-[30px] mb-[30px] ml-4 mr-4 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded text-center">
                Aucune bouteille dans ce cellier
            </div>
            @endforelse
        </div>

</section>
@endsection