import { useState } from 'react';
import { Link } from 'react-router';
import { Search, Filter, Star, MapPin, ChevronDown } from 'lucide-react';

const mockChefs = [
  {
    id: 1,
    name: 'Elena Marchetti',
    specialty: 'Cucina Mediterranea',
    rating: 4.9,
    reviews: 127,
    image: 'https://images.unsplash.com/photo-1750943082012-efe6d2fd9e45?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    location: 'Milano',
    priceRange: '€€€',
    description: 'Chef stellata specializzata in cucina mediterranea moderna con ingredienti di stagione.',
    cuisine: 'mediterranea',
    pricePerPerson: 120
  },
  {
    id: 2,
    name: 'Takeshi Yamamoto',
    specialty: 'Cucina Giapponese',
    rating: 5.0,
    reviews: 89,
    image: 'https://images.unsplash.com/photo-1764397514739-57a0ac81330d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    location: 'Roma',
    priceRange: '€€€€',
    description: 'Maestro sushi con 15 anni di esperienza, formazione a Tokyo.',
    cuisine: 'giapponese',
    pricePerPerson: 180
  },
  {
    id: 3,
    name: 'Maria Santoro',
    specialty: 'Cucina Vegana',
    rating: 4.8,
    reviews: 156,
    image: 'https://images.unsplash.com/photo-1750943079478-ae516c4133cd?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    location: 'Firenze',
    priceRange: '€€',
    description: 'Chef vegana innovativa, crea piatti gourmet 100% vegetali.',
    cuisine: 'vegana',
    pricePerPerson: 85
  },
  {
    id: 4,
    name: 'Pierre Dubois',
    specialty: 'Haute Cuisine',
    rating: 4.9,
    reviews: 203,
    image: 'https://images.unsplash.com/photo-1759741558258-f24a3e847968?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    location: 'Torino',
    priceRange: '€€€€',
    description: 'Chef francese con esperienza in ristoranti 3 stelle Michelin.',
    cuisine: 'fusion',
    pricePerPerson: 200
  },
  {
    id: 5,
    name: 'Giovanni Russo',
    specialty: 'Cucina Italiana Tradizionale',
    rating: 4.7,
    reviews: 94,
    image: 'https://images.unsplash.com/photo-1775513181406-992e0e1d4717?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    location: 'Napoli',
    priceRange: '€€',
    description: 'Esperto di cucina napoletana e tradizioni culinarie del sud Italia.',
    cuisine: 'italiana',
    pricePerPerson: 75
  },
  {
    id: 6,
    name: 'Sofia Chen',
    specialty: 'Cucina Fusion Asiatica',
    rating: 4.9,
    reviews: 112,
    image: 'https://images.unsplash.com/photo-1750943082166-8753023ff83a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    location: 'Milano',
    priceRange: '€€€',
    description: 'Chef fusion che combina tecniche asiatiche e italiane con risultati sorprendenti.',
    cuisine: 'fusion',
    pricePerPerson: 135
  }
];

const cuisineFilters = ['Tutte', 'Italiana', 'Giapponese', 'Vegana', 'Mediterranea', 'Fusion', 'Pasticceria'];
const locations = ['Tutte', 'Milano', 'Roma', 'Firenze', 'Torino', 'Napoli'];
const priceRanges = ['Tutti', '€ (0-50€)', '€€ (50-100€)', '€€€ (100-150€)', '€€€€ (150€+)'];

export function SearchChefs() {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCuisine, setSelectedCuisine] = useState('Tutte');
  const [selectedLocation, setSelectedLocation] = useState('Tutte');
  const [selectedPriceRange, setSelectedPriceRange] = useState('Tutti');
  const [showFilters, setShowFilters] = useState(false);

  const filteredChefs = mockChefs.filter(chef => {
    const matchesSearch = chef.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                         chef.specialty.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesCuisine = selectedCuisine === 'Tutte' ||
                          chef.cuisine.toLowerCase() === selectedCuisine.toLowerCase() ||
                          chef.specialty.toLowerCase().includes(selectedCuisine.toLowerCase());
    const matchesLocation = selectedLocation === 'Tutte' || chef.location === selectedLocation;

    let matchesPrice = true;
    if (selectedPriceRange !== 'Tutti') {
      const price = chef.pricePerPerson;
      if (selectedPriceRange === '€ (0-50€)') matchesPrice = price <= 50;
      else if (selectedPriceRange === '€€ (50-100€)') matchesPrice = price > 50 && price <= 100;
      else if (selectedPriceRange === '€€€ (100-150€)') matchesPrice = price > 100 && price <= 150;
      else if (selectedPriceRange === '€€€€ (150€+)') matchesPrice = price > 150;
    }

    return matchesSearch && matchesCuisine && matchesLocation && matchesPrice;
  });

  return (
    <div className="min-h-screen bg-background">
      <div className="bg-primary text-primary-foreground py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="font-display text-5xl md:text-6xl mb-4">
            Trova il tuo Chef
          </h1>
          <p className="text-xl text-primary-foreground/80 max-w-2xl">
            Scopri chef professionisti per ogni occasione e gusto culinario
          </p>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
        <div className="bg-white rounded-2xl shadow-xl p-6 mb-8">
          <div className="flex flex-col md:flex-row gap-4">
            <div className="flex-1 relative">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
              <input
                type="text"
                placeholder="Cerca per nome o specialità..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-12 pr-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none transition-colors"
              />
            </div>
            <button
              onClick={() => setShowFilters(!showFilters)}
              className="flex items-center justify-center gap-2 px-6 py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
            >
              <Filter className="w-5 h-5" />
              <span className="font-medium">Filtri</span>
              <ChevronDown className={`w-4 h-4 transition-transform ${showFilters ? 'rotate-180' : ''}`} />
            </button>
          </div>

          {showFilters && (
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 pt-6 border-t border-primary/10 animate-in slide-in-from-top-2 duration-200">
              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Tipo di Cucina
                </label>
                <select
                  value={selectedCuisine}
                  onChange={(e) => setSelectedCuisine(e.target.value)}
                  className="w-full px-4 py-2 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                >
                  {cuisineFilters.map(cuisine => (
                    <option key={cuisine} value={cuisine}>{cuisine}</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Località
                </label>
                <select
                  value={selectedLocation}
                  onChange={(e) => setSelectedLocation(e.target.value)}
                  className="w-full px-4 py-2 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                >
                  {locations.map(location => (
                    <option key={location} value={location}>{location}</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Fascia di Prezzo
                </label>
                <select
                  value={selectedPriceRange}
                  onChange={(e) => setSelectedPriceRange(e.target.value)}
                  className="w-full px-4 py-2 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                >
                  {priceRanges.map(range => (
                    <option key={range} value={range}>{range}</option>
                  ))}
                </select>
              </div>
            </div>
          )}
        </div>

        <div className="mb-6 flex items-center justify-between">
          <p className="text-muted-foreground">
            {filteredChefs.length} chef {filteredChefs.length === 1 ? 'trovato' : 'trovati'}
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 pb-16">
          {filteredChefs.map((chef, index) => (
            <Link
              key={chef.id}
              to={`/chefs/${chef.id}`}
              className="group bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 animate-in fade-in slide-in-from-bottom-4"
              style={{ animationDelay: `${index * 50}ms` }}
            >
              <div className="relative overflow-hidden aspect-[4/3]">
                <img
                  src={chef.image}
                  alt={chef.name}
                  className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-primary/60 to-transparent" />
                <div className="absolute top-4 right-4 px-3 py-1 bg-white/95 backdrop-blur-sm rounded-full text-sm font-medium text-primary">
                  {chef.priceRange}
                </div>
              </div>

              <div className="p-6">
                <div className="flex items-start justify-between mb-3">
                  <div className="flex-1">
                    <h3 className="font-display text-2xl text-primary mb-1 group-hover:text-accent transition-colors">
                      {chef.name}
                    </h3>
                    <p className="text-muted-foreground">{chef.specialty}</p>
                  </div>
                </div>

                <p className="text-sm text-foreground/70 mb-4 line-clamp-2">
                  {chef.description}
                </p>

                <div className="flex items-center justify-between pt-4 border-t border-primary/10">
                  <div className="flex items-center gap-2">
                    <MapPin className="w-4 h-4 text-muted-foreground" />
                    <span className="text-sm text-muted-foreground">{chef.location}</span>
                  </div>
                  <div className="flex items-center gap-1">
                    <Star className="w-4 h-4 fill-accent text-accent" />
                    <span className="font-semibold text-sm">{chef.rating}</span>
                    <span className="text-sm text-muted-foreground">({chef.reviews})</span>
                  </div>
                </div>

                <div className="mt-4 text-sm text-muted-foreground">
                  A partire da <span className="font-semibold text-accent">€{chef.pricePerPerson}</span>/persona
                </div>
              </div>
            </Link>
          ))}
        </div>

        {filteredChefs.length === 0 && (
          <div className="text-center py-16">
            <div className="w-20 h-20 bg-muted/50 rounded-full flex items-center justify-center mx-auto mb-4">
              <Search className="w-10 h-10 text-muted-foreground" />
            </div>
            <h3 className="font-display text-2xl text-primary mb-2">
              Nessun chef trovato
            </h3>
            <p className="text-muted-foreground mb-6">
              Prova a modificare i filtri di ricerca
            </p>
            <button
              onClick={() => {
                setSearchQuery('');
                setSelectedCuisine('Tutte');
                setSelectedLocation('Tutte');
                setSelectedPriceRange('Tutti');
              }}
              className="px-6 py-3 bg-accent text-white rounded-lg font-medium hover:bg-accent/90 transition-colors"
            >
              Reimposta filtri
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
