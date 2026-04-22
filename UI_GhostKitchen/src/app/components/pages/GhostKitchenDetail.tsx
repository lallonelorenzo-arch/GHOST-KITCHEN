import { useParams, Link } from 'react-router';
import { MapPin, Users, Clock, Star, ChevronLeft, Check } from 'lucide-react';
import { useState } from 'react';

const mockKitchen = {
  id: 1,
  name: 'Kitchen Lab Milano Centro',
  location: 'Milano, Via Torino 45',
  fullAddress: 'Via Torino 45, 20121 Milano (MI)',
  rating: 4.8,
  reviews: 89,
  pricePerHour: 45,
  capacity: 8,
  size: 120,
  description: 'Cucina professionale completamente attrezzata nel cuore di Milano. Ideale per chef professionisti, catering, food blogger e progetti culinari. Spazio ampio e luminoso con tutte le attrezzature necessarie per la preparazione di piatti di alta qualità.',
  gallery: [
    'https://images.unsplash.com/photo-1767785990437-dfe1fe516fe8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200',
    'https://images.unsplash.com/photo-1771360963016-1408c2de12c4?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    'https://images.unsplash.com/photo-1762922425168-616c0d654a75?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    'https://images.unsplash.com/photo-1761484118042-765eaaabc7e1?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800'
  ],
  equipment: {
    'Cottura': [
      'Piano cottura professionale 6 fuochi',
      'Forno ventilato elettrico',
      'Forno a microonde',
      'Piastra in ghisa',
      'Friggitrice professionale'
    ],
    'Refrigerazione': [
      'Frigorifero industriale 600L',
      'Congelatore verticale 400L',
      'Abbattitore di temperatura',
      'Tavolo refrigerato'
    ],
    'Preparazione': [
      'Banco lavoro acciaio inox (3 postazioni)',
      'Impastatrice planetaria 20L',
      'Robot da cucina multifunzione',
      'Affettatrice professionale',
      'Tritacarne elettrico'
    ],
    'Lavaggio': [
      'Lavastoviglie industriale',
      'Doppio lavandino inox',
      'Scaffalature in acciaio'
    ]
  },
  amenities: [
    'Wi-Fi fibra ottica',
    'Area relax/pausa',
    'Bagno privato',
    'Parcheggio convenzionato',
    'Accesso H24 con prenotazione',
    'Sistema di sicurezza',
    'Climatizzazione',
    'Illuminazione LED professionale'
  ],
  rules: [
    'Rispetto rigoroso delle norme HACCP',
    'Pulizia completa alla fine di ogni turno',
    'Divieto di fumo all\'interno',
    'Massima capacità: 8 persone',
    'Prenotazione minima: 3 ore'
  ],
  availableSlots: [
    { time: 'Mattina (8:00 - 13:00)', available: true },
    { time: 'Pomeriggio (14:00 - 19:00)', available: true },
    { time: 'Sera (19:00 - 24:00)', available: false },
    { time: 'Notte (00:00 - 6:00)', available: true }
  ],
  pricing: [
    { hours: '3-5 ore', price: 45, total: '135-225€' },
    { hours: '6-10 ore', price: 42, total: '252-420€' },
    { hours: 'Giornata intera (10+ ore)', price: 38, total: 'da 380€' }
  ],
  reviews: [
    {
      id: 1,
      author: 'Luca Moretti - Chef Privato',
      rating: 5,
      date: '2026-04-01',
      comment: 'Cucina eccellente, attrezzature di qualità professionale. Ho utilizzato lo spazio per un catering da 50 persone e tutto è andato alla perfezione. Tornerò sicuramente!',
      verified: true
    },
    {
      id: 2,
      author: 'Sara Colombo - Food Blogger',
      rating: 4.5,
      date: '2026-03-20',
      comment: 'Perfetta per le mie sessioni di shooting culinario. Illuminazione ottima e spazio ben organizzato. Unico appunto: sarebbe utile avere più stoviglie per il plating.',
      verified: true
    },
    {
      id: 3,
      author: 'Antonio Greco - Pasticciere',
      rating: 5,
      date: '2026-03-10',
      comment: 'Utilizzo questa cucina settimanalmente per la produzione dei miei dolci. Impastatrice planetaria eccezionale e abbattitore indispensabile. Staff disponibile e professionale.',
      verified: true
    }
  ]
};

export function GhostKitchenDetail() {
  const { id } = useParams();
  const [activeTab, setActiveTab] = useState<'equipment' | 'reviews' | 'pricing'>('equipment');

  return (
    <div className="min-h-screen bg-background">
      <div className="relative h-[60vh] overflow-hidden">
        <img
          src={mockKitchen.gallery[0]}
          alt={mockKitchen.name}
          className="w-full h-full object-cover"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-primary via-primary/50 to-transparent" />

        <Link
          to="/ghost-kitchens"
          className="absolute top-8 left-8 flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-md text-white rounded-lg hover:bg-white/20 transition-colors"
        >
          <ChevronLeft className="w-5 h-5" />
          Torna alle cucine
        </Link>

        <div className="absolute bottom-0 left-0 right-0 p-8 md:p-12 text-white">
          <div className="max-w-7xl mx-auto">
            <div className="flex items-center gap-3 mb-4">
              <div className="flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full">
                <Star className="w-4 h-4 fill-accent text-accent" />
                <span className="font-semibold">{mockKitchen.rating}</span>
                <span className="text-white/80">({mockKitchen.reviews.length} recensioni)</span>
              </div>
            </div>

            <h1 className="font-display text-5xl md:text-7xl mb-3">
              {mockKitchen.name}
            </h1>
            <div className="flex items-center gap-2 text-white/90 text-lg">
              <MapPin className="w-5 h-5" />
              <span>{mockKitchen.fullAddress}</span>
            </div>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
          <div className="lg:col-span-2">
            <div className="mb-8">
              <h2 className="font-display text-3xl text-primary mb-4">
                Descrizione
              </h2>
              <p className="text-lg text-foreground/80 leading-relaxed">
                {mockKitchen.description}
              </p>
            </div>

            <div className="grid grid-cols-3 gap-6 mb-12">
              <div className="text-center p-6 bg-white rounded-xl border border-primary/10">
                <div className="w-12 h-12 bg-accent/10 rounded-full flex items-center justify-center mx-auto mb-3">
                  <Users className="w-6 h-6 text-accent" />
                </div>
                <p className="text-2xl font-semibold text-primary mb-1">{mockKitchen.capacity}</p>
                <p className="text-sm text-muted-foreground">Persone</p>
              </div>
              <div className="text-center p-6 bg-white rounded-xl border border-primary/10">
                <div className="w-12 h-12 bg-accent/10 rounded-full flex items-center justify-center mx-auto mb-3">
                  <div className="font-display font-semibold text-accent">m²</div>
                </div>
                <p className="text-2xl font-semibold text-primary mb-1">{mockKitchen.size}</p>
                <p className="text-sm text-muted-foreground">Metratura</p>
              </div>
              <div className="text-center p-6 bg-white rounded-xl border border-primary/10">
                <div className="w-12 h-12 bg-accent/10 rounded-full flex items-center justify-center mx-auto mb-3">
                  <Clock className="w-6 h-6 text-accent" />
                </div>
                <p className="text-2xl font-semibold text-primary mb-1">H24</p>
                <p className="text-sm text-muted-foreground">Disponibilità</p>
              </div>
            </div>

            <div className="mb-8">
              <h3 className="font-display text-2xl text-primary mb-4">Galleria</h3>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                {mockKitchen.gallery.map((image, index) => (
                  <div
                    key={index}
                    className="relative overflow-hidden rounded-xl aspect-video animate-in fade-in zoom-in-95 duration-300"
                    style={{ animationDelay: `${index * 50}ms` }}
                  >
                    <img
                      src={image}
                      alt={`Gallery ${index + 1}`}
                      className="w-full h-full object-cover hover:scale-110 transition-transform duration-500"
                    />
                  </div>
                ))}
              </div>
            </div>

            <div className="flex gap-6 mb-8 border-b border-primary/10">
              {(['equipment', 'pricing', 'reviews'] as const).map(tab => (
                <button
                  key={tab}
                  onClick={() => setActiveTab(tab)}
                  className={`pb-4 font-medium capitalize transition-colors ${
                    activeTab === tab
                      ? 'text-accent border-b-2 border-accent'
                      : 'text-muted-foreground hover:text-primary'
                  }`}
                >
                  {tab === 'equipment' ? 'Attrezzature' : tab === 'pricing' ? 'Prezzi' : 'Recensioni'}
                </button>
              ))}
            </div>

            {activeTab === 'equipment' && (
              <div className="animate-in fade-in slide-in-from-right-4 duration-300">
                <div className="space-y-8">
                  {Object.entries(mockKitchen.equipment).map(([category, items]) => (
                    <div key={category}>
                      <h3 className="font-display text-xl text-primary mb-4">{category}</h3>
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                        {items.map((item, index) => (
                          <div
                            key={index}
                            className="flex items-center gap-3 p-4 bg-white rounded-lg border border-primary/10"
                          >
                            <Check className="w-5 h-5 text-secondary flex-shrink-0" />
                            <span className="text-foreground/80">{item}</span>
                          </div>
                        ))}
                      </div>
                    </div>
                  ))}
                </div>

                <div className="mt-8 p-6 bg-muted/30 rounded-xl">
                  <h3 className="font-display text-xl text-primary mb-4">Servizi Aggiuntivi</h3>
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                    {mockKitchen.amenities.map((amenity, index) => (
                      <div
                        key={index}
                        className="flex items-center gap-2 text-sm text-foreground/70"
                      >
                        <div className="w-1.5 h-1.5 bg-accent rounded-full" />
                        {amenity}
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            )}

            {activeTab === 'pricing' && (
              <div className="animate-in fade-in slide-in-from-right-4 duration-300">
                <div className="space-y-4 mb-8">
                  {mockKitchen.pricing.map((tier, index) => (
                    <div
                      key={index}
                      className="p-6 bg-white rounded-xl border-2 border-primary/10 hover:border-accent/50 transition-colors"
                    >
                      <div className="flex items-center justify-between mb-2">
                        <h4 className="font-display text-xl text-primary">{tier.hours}</h4>
                        <div className="text-right">
                          <p className="text-3xl font-semibold text-accent">€{tier.price}<span className="text-base text-muted-foreground">/h</span></p>
                          <p className="text-sm text-muted-foreground">Totale: {tier.total}</p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>

                <div className="p-6 bg-accent/5 border border-accent/20 rounded-xl">
                  <h3 className="font-display text-xl text-primary mb-4">Regole della Cucina</h3>
                  <ul className="space-y-3">
                    {mockKitchen.rules.map((rule, index) => (
                      <li key={index} className="flex items-start gap-3 text-foreground/80">
                        <div className="w-1.5 h-1.5 bg-accent rounded-full mt-2 flex-shrink-0" />
                        <span>{rule}</span>
                      </li>
                    ))}
                  </ul>
                </div>
              </div>
            )}

            {activeTab === 'reviews' && (
              <div className="animate-in fade-in slide-in-from-right-4 duration-300">
                <div className="flex items-center justify-between mb-8">
                  <h2 className="font-display text-3xl text-primary">
                    Recensioni
                  </h2>
                  <div className="flex items-center gap-2">
                    <Star className="w-6 h-6 fill-accent text-accent" />
                    <span className="text-2xl font-semibold">{mockKitchen.rating}</span>
                    <span className="text-muted-foreground">/ 5.0</span>
                  </div>
                </div>

                <div className="space-y-6">
                  {mockKitchen.reviews.map((review, index) => (
                    <div
                      key={review.id}
                      className="p-6 bg-white rounded-xl border border-primary/10 animate-in fade-in slide-in-from-bottom-2 duration-300"
                      style={{ animationDelay: `${index * 100}ms` }}
                    >
                      <div className="flex items-start justify-between mb-4">
                        <div>
                          <div className="flex items-center gap-3 mb-1">
                            <h4 className="font-semibold text-primary">{review.author}</h4>
                            {review.verified && (
                              <span className="px-2 py-0.5 bg-secondary/20 text-secondary text-xs rounded-full">
                                Verificata
                              </span>
                            )}
                          </div>
                          <p className="text-sm text-muted-foreground">
                            {new Date(review.date).toLocaleDateString('it-IT', {
                              year: 'numeric',
                              month: 'long',
                              day: 'numeric'
                            })}
                          </p>
                        </div>
                        <div className="flex items-center gap-1">
                          {Array.from({ length: 5 }).map((_, i) => (
                            <Star
                              key={i}
                              className={`w-4 h-4 ${
                                i < Math.floor(review.rating)
                                  ? 'fill-accent text-accent'
                                  : 'text-muted'
                              }`}
                            />
                          ))}
                        </div>
                      </div>
                      <p className="text-foreground/80 leading-relaxed">{review.comment}</p>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>

          <div className="lg:col-span-1">
            <div className="sticky top-24 bg-white rounded-2xl border-2 border-primary/10 p-6 shadow-xl">
              <div className="text-center mb-6">
                <p className="text-muted-foreground mb-2">A partire da</p>
                <p className="font-display text-4xl text-accent mb-1">
                  €{mockKitchen.pricePerHour}
                </p>
                <p className="text-sm text-muted-foreground">per ora</p>
              </div>

              <div className="space-y-3 mb-6 py-6 border-y border-primary/10">
                <h4 className="font-medium text-primary mb-3">Disponibilità Oggi</h4>
                {mockKitchen.availableSlots.map((slot, index) => (
                  <div
                    key={index}
                    className={`flex items-center justify-between p-3 rounded-lg ${
                      slot.available
                        ? 'bg-secondary/10 border border-secondary/20'
                        : 'bg-muted/30 border border-muted'
                    }`}
                  >
                    <span className={`text-sm ${slot.available ? 'text-foreground' : 'text-muted-foreground'}`}>
                      {slot.time}
                    </span>
                    <span className={`text-xs font-medium ${slot.available ? 'text-secondary' : 'text-muted-foreground'}`}>
                      {slot.available ? 'Disponibile' : 'Occupato'}
                    </span>
                  </div>
                ))}
              </div>

              <Link
                to={`/booking/kitchen/${mockKitchen.id}`}
                className="block w-full py-4 bg-accent text-white text-center rounded-lg font-semibold hover:bg-accent/90 transition-all hover:shadow-lg hover:shadow-accent/20 mb-3"
              >
                Prenota Ora
              </Link>

              <button className="w-full py-3 border-2 border-primary/20 text-primary rounded-lg font-medium hover:bg-muted/30 transition-colors">
                Contatta Gestore
              </button>

              <p className="text-xs text-muted-foreground text-center mt-4">
                Cancellazione gratuita fino a 24h prima della prenotazione
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
