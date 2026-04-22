import { useState } from 'react';
import { Link } from 'react-router';
import { Search, MapPin, Users, Clock, Euro, ChevronDown } from 'lucide-react';

const mockKitchens = [
  {
    id: 1,
    name: 'Kitchen Lab Milano Centro',
    location: 'Milano, Via Torino 45',
    city: 'Milano',
    image: 'https://images.unsplash.com/photo-1767785990437-dfe1fe516fe8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200',
    rating: 4.8,
    reviews: 89,
    pricePerHour: 45,
    capacity: 8,
    size: 120,
    equipment: ['Forno professionale', 'Piano cottura 6 fuochi', 'Frigorifero industriale', 'Lavastoviglie'],
    availableSlots: ['Mattina', 'Pomeriggio', 'Sera']
  },
  {
    id: 2,
    name: 'Pro Kitchen Roma',
    location: 'Roma, Via dei Serpenti 28',
    city: 'Roma',
    image: 'https://images.unsplash.com/photo-1771360963016-1408c2de12c4?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200',
    rating: 4.9,
    reviews: 124,
    pricePerHour: 50,
    capacity: 10,
    size: 150,
    equipment: ['Forno a legna', 'Piano cottura induzione', 'Abbattitore', 'Impastatrice planetaria'],
    availableSlots: ['Mattina', 'Pomeriggio']
  },
  {
    id: 3,
    name: 'Culinary Space Firenze',
    location: 'Firenze, Via del Corso 12',
    city: 'Firenze',
    image: 'https://images.unsplash.com/photo-1762922425168-616c0d654a75?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200',
    rating: 4.7,
    reviews: 67,
    pricePerHour: 40,
    capacity: 6,
    size: 90,
    equipment: ['Forno elettrico', 'Piano cottura gas', 'Celle frigorifere', 'Banco lavoro acciaio'],
    availableSlots: ['Mattina', 'Sera']
  },
  {
    id: 4,
    name: 'Kitchen Hub Torino',
    location: 'Torino, Corso Francia 89',
    city: 'Torino',
    image: 'https://images.unsplash.com/photo-1761484118042-765eaaabc7e1?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200',
    rating: 4.6,
    reviews: 52,
    pricePerHour: 38,
    capacity: 5,
    size: 80,
    equipment: ['Forno ventilato', 'Piano cottura elettrico', 'Frigorifero', 'Lavandino doppio'],
    availableSlots: ['Mattina', 'Pomeriggio', 'Sera']
  }
];

const cities = ['Tutte', 'Milano', 'Roma', 'Firenze', 'Torino', 'Napoli'];
const timeSlots = ['Tutti', 'Mattina', 'Pomeriggio', 'Sera'];
const priceRanges = ['Tutti', '< €40/h', '€40-50/h', '> €50/h'];

export function GhostKitchens() {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCity, setSelectedCity] = useState('Tutte');
  const [selectedTimeSlot, setSelectedTimeSlot] = useState('Tutti');
  const [selectedPriceRange, setSelectedPriceRange] = useState('Tutti');
  const [showFilters, setShowFilters] = useState(false);

  const filteredKitchens = mockKitchens.filter(kitchen => {
    const matchesSearch = kitchen.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                         kitchen.location.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesCity = selectedCity === 'Tutte' || kitchen.city === selectedCity;
    const matchesTimeSlot = selectedTimeSlot === 'Tutti' ||
                           kitchen.availableSlots.includes(selectedTimeSlot);

    let matchesPrice = true;
    if (selectedPriceRange !== 'Tutti') {
      const price = kitchen.pricePerHour;
      if (selectedPriceRange === '< €40/h') matchesPrice = price < 40;
      else if (selectedPriceRange === '€40-50/h') matchesPrice = price >= 40 && price <= 50;
      else if (selectedPriceRange === '> €50/h') matchesPrice = price > 50;
    }

    return matchesSearch && matchesCity && matchesTimeSlot && matchesPrice;
  });

  return (
    <div className="min-h-screen bg-background">
      <div className="bg-primary text-primary-foreground py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="font-display text-5xl md:text-6xl mb-4">
            Ghost Kitchen
          </h1>
          <p className="text-xl text-primary-foreground/80 max-w-2xl">
            Cucine professionali attrezzate disponibili a ore per chef e progetti culinari
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
                placeholder="Cerca per nome o località..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-12 pr-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none transition-colors"
              />
            </div>
            <button
              onClick={() => setShowFilters(!showFilters)}
              className="flex items-center justify-center gap-2 px-6 py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
            >
              <ChevronDown className={`w-5 h-5 transition-transform ${showFilters ? 'rotate-180' : ''}`} />
              <span className="font-medium">Filtri</span>
            </button>
          </div>

          {showFilters && (
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 pt-6 border-t border-primary/10 animate-in slide-in-from-top-2 duration-200">
              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Città
                </label>
                <select
                  value={selectedCity}
                  onChange={(e) => setSelectedCity(e.target.value)}
                  className="w-full px-4 py-2 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                >
                  {cities.map(city => (
                    <option key={city} value={city}>{city}</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Fascia Oraria
                </label>
                <select
                  value={selectedTimeSlot}
                  onChange={(e) => setSelectedTimeSlot(e.target.value)}
                  className="w-full px-4 py-2 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                >
                  {timeSlots.map(slot => (
                    <option key={slot} value={slot}>{slot}</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-foreground mb-2">
                  Prezzo Orario
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
            {filteredKitchens.length} {filteredKitchens.length === 1 ? 'cucina trovata' : 'cucine trovate'}
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 pb-16">
          {filteredKitchens.map((kitchen, index) => (
            <Link
              key={kitchen.id}
              to={`/ghost-kitchens/${kitchen.id}`}
              className="group bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 animate-in fade-in slide-in-from-bottom-4"
              style={{ animationDelay: `${index * 50}ms` }}
            >
              <div className="relative overflow-hidden aspect-[16/9]">
                <img
                  src={kitchen.image}
                  alt={kitchen.name}
                  className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-primary/60 to-transparent" />
                <div className="absolute bottom-4 left-4 right-4 text-white">
                  <div className="flex items-center gap-2">
                    {kitchen.availableSlots.map(slot => (
                      <span
                        key={slot}
                        className="px-2 py-1 bg-white/20 backdrop-blur-sm rounded text-xs"
                      >
                        {slot}
                      </span>
                    ))}
                  </div>
                </div>
                <div className="absolute top-4 right-4 px-4 py-2 bg-accent text-white rounded-lg font-semibold">
                  €{kitchen.pricePerHour}/h
                </div>
              </div>

              <div className="p-6">
                <h3 className="font-display text-2xl text-primary mb-2 group-hover:text-accent transition-colors">
                  {kitchen.name}
                </h3>

                <div className="flex items-center gap-2 mb-4 text-muted-foreground">
                  <MapPin className="w-4 h-4" />
                  <span className="text-sm">{kitchen.location}</span>
                </div>

                <div className="grid grid-cols-3 gap-4 py-4 border-y border-primary/10 mb-4">
                  <div className="text-center">
                    <Users className="w-5 h-5 text-accent mx-auto mb-1" />
                    <p className="text-xs text-muted-foreground mb-1">Capacità</p>
                    <p className="font-semibold text-sm">{kitchen.capacity} persone</p>
                  </div>
                  <div className="text-center">
                    <div className="w-5 h-5 text-accent mx-auto mb-1 flex items-center justify-center font-semibold">
                      m²
                    </div>
                    <p className="text-xs text-muted-foreground mb-1">Metratura</p>
                    <p className="font-semibold text-sm">{kitchen.size} m²</p>
                  </div>
                  <div className="text-center">
                    <div className="flex items-center justify-center gap-0.5 mb-1">
                      {Array.from({ length: 5 }).map((_, i) => (
                        <div
                          key={i}
                          className={`w-1 h-1 rounded-full ${
                            i < Math.floor(kitchen.rating) ? 'bg-accent' : 'bg-muted'
                          }`}
                        />
                      ))}
                    </div>
                    <p className="text-xs text-muted-foreground mb-1">Valutazione</p>
                    <p className="font-semibold text-sm">{kitchen.rating}/5</p>
                  </div>
                </div>

                <div className="mb-4">
                  <p className="text-xs font-medium text-muted-foreground mb-2">
                    Attrezzature principali:
                  </p>
                  <div className="flex flex-wrap gap-2">
                    {kitchen.equipment.slice(0, 3).map((equip, i) => (
                      <span
                        key={i}
                        className="text-xs px-2 py-1 bg-muted/50 rounded-full text-foreground/70"
                      >
                        {equip}
                      </span>
                    ))}
                    {kitchen.equipment.length > 3 && (
                      <span className="text-xs px-2 py-1 bg-accent/10 text-accent rounded-full">
                        +{kitchen.equipment.length - 3} altro
                      </span>
                    )}
                  </div>
                </div>

                <div className="flex items-center justify-between pt-4 border-t border-primary/10">
                  <span className="text-sm text-muted-foreground">
                    {kitchen.reviews} recensioni
                  </span>
                  <span className="text-sm font-medium text-accent">
                    Vedi dettagli →
                  </span>
                </div>
              </div>
            </Link>
          ))}
        </div>

        {filteredKitchens.length === 0 && (
          <div className="text-center py-16">
            <div className="w-20 h-20 bg-muted/50 rounded-full flex items-center justify-center mx-auto mb-4">
              <Search className="w-10 h-10 text-muted-foreground" />
            </div>
            <h3 className="font-display text-2xl text-primary mb-2">
              Nessuna cucina trovata
            </h3>
            <p className="text-muted-foreground mb-6">
              Prova a modificare i filtri di ricerca
            </p>
            <button
              onClick={() => {
                setSearchQuery('');
                setSelectedCity('Tutte');
                setSelectedTimeSlot('Tutti');
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
