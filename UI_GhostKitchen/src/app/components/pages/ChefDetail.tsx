import { useParams, Link } from 'react-router';
import { Star, MapPin, Award, Clock, Users, Calendar, ChevronLeft } from 'lucide-react';
import { useState } from 'react';

const mockChef = {
  id: 1,
  name: 'Elena Marchetti',
  specialty: 'Cucina Mediterranea',
  rating: 4.9,
  reviews: 127,
  image: 'https://images.unsplash.com/photo-1750943082012-efe6d2fd9e45?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200',
  location: 'Milano',
  priceRange: '€€€',
  pricePerPerson: 120,
  bio: 'Chef stellata con oltre 15 anni di esperienza nella cucina mediterranea moderna. Specializzata in piatti che esaltano i sapori tradizionali con tecniche innovative e presentazioni artistiche. Laureata presso l\'Istituto Culinario Italiano e con esperienza in ristoranti stellati in Italia e Francia.',
  certifications: ['Stella Michelin 2023', 'Certificazione HACCP', 'Sommelier AIS Livello 2'],
  experience: '15+ anni',
  languages: ['Italiano', 'Inglese', 'Francese'],
  minGuests: 4,
  maxGuests: 20,
  gallery: [
    'https://images.unsplash.com/photo-1750943082012-efe6d2fd9e45?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    'https://images.unsplash.com/photo-1750943081248-833d71a2ab8e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    'https://images.unsplash.com/photo-1750943082020-4969b2a63084?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    'https://images.unsplash.com/photo-1750943079478-ae516c4133cd?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    'https://images.unsplash.com/photo-1750943082166-8753023ff83a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    'https://images.unsplash.com/photo-1775513181406-992e0e1d4717?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800'
  ],
  menus: [
    {
      id: 1,
      name: 'Menu Degustazione Mediterraneo',
      price: 120,
      courses: [
        'Antipasto: Crudo di mare con agrumi e olio EVO',
        'Primo: Risotto ai frutti di mare con limone di Sorrento',
        'Secondo: Branzino al cartoccio con verdure di stagione',
        'Dolce: Panna cotta al limoncello con frutti rossi'
      ]
    },
    {
      id: 2,
      name: 'Menu Vegetariano Gourmet',
      price: 95,
      courses: [
        'Antipasto: Insalata di quinoa con verdure grigliate',
        'Primo: Ravioli di ricotta e spinaci al burro e salvia',
        'Secondo: Parmigiana di melanzane rivisitata',
        'Dolce: Tiramisù alle fragole'
      ]
    },
    {
      id: 3,
      name: 'Menu Premium Stellato',
      price: 180,
      courses: [
        'Amuse-bouche: Tartare di gambero rosso',
        'Antipasto: Carpaccio di manzo con tartufo nero',
        'Primo: Paccheri alla crema di scampi',
        'Secondo: Filetto di vitello con riduzione di Barolo',
        'Pre-dessert: Sorbetto al limone',
        'Dolce: Soufflé al cioccolato fondente'
      ]
    }
  ],
  reviews: [
    {
      id: 1,
      author: 'Marco Bianchi',
      rating: 5,
      date: '2026-03-28',
      comment: 'Esperienza incredibile! Elena ha preparato un menu stellato per il nostro anniversario. Ogni piatto era un\'opera d\'arte, sia nel gusto che nella presentazione. Professionalità assoluta.',
      verified: true
    },
    {
      id: 2,
      author: 'Laura Ferri',
      rating: 5,
      date: '2026-03-15',
      comment: 'Chef eccezionale. Ha adattato il menu alle nostre esigenze alimentari senza compromettere la qualità. I nostri ospiti non smettono di parlarne!',
      verified: true
    },
    {
      id: 3,
      author: 'Andrea Romano',
      rating: 4.8,
      date: '2026-02-20',
      comment: 'Ottima esperienza, piatti raffinati e ben bilanciati. Unico piccolo appunto: avrei gradito porzioni leggermente più abbondanti, ma è una questione di gusti personali.',
      verified: true
    }
  ]
};

export function ChefDetail() {
  const { id } = useParams();
  const [selectedMenu, setSelectedMenu] = useState(mockChef.menus[0]);
  const [activeTab, setActiveTab] = useState<'menu' | 'reviews' | 'about'>('menu');

  return (
    <div className="min-h-screen bg-background">
      <div className="relative h-[60vh] overflow-hidden">
        <img
          src={mockChef.image}
          alt={mockChef.name}
          className="w-full h-full object-cover"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-primary via-primary/50 to-transparent" />

        <Link
          to="/chefs"
          className="absolute top-8 left-8 flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-md text-white rounded-lg hover:bg-white/20 transition-colors"
        >
          <ChevronLeft className="w-5 h-5" />
          Torna alla ricerca
        </Link>

        <div className="absolute bottom-0 left-0 right-0 p-8 md:p-12 text-white">
          <div className="max-w-7xl mx-auto">
            <div className="flex items-center gap-3 mb-4">
              <div className="flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full">
                <Star className="w-4 h-4 fill-accent text-accent" />
                <span className="font-semibold">{mockChef.rating}</span>
                <span className="text-white/80">({mockChef.reviews.length} recensioni)</span>
              </div>
              <div className="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm">
                {mockChef.priceRange}
              </div>
            </div>

            <h1 className="font-display text-5xl md:text-7xl mb-3">
              {mockChef.name}
            </h1>
            <p className="text-2xl text-white/90 mb-4">{mockChef.specialty}</p>
            <div className="flex items-center gap-2 text-white/80">
              <MapPin className="w-5 h-5" />
              <span className="text-lg">{mockChef.location}</span>
            </div>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
          <div className="lg:col-span-2">
            <div className="flex gap-6 mb-8 border-b border-primary/10">
              {(['menu', 'reviews', 'about'] as const).map(tab => (
                <button
                  key={tab}
                  onClick={() => setActiveTab(tab)}
                  className={`pb-4 font-medium capitalize transition-colors ${
                    activeTab === tab
                      ? 'text-accent border-b-2 border-accent'
                      : 'text-muted-foreground hover:text-primary'
                  }`}
                >
                  {tab === 'menu' ? 'Menù' : tab === 'reviews' ? 'Recensioni' : 'Chi Sono'}
                </button>
              ))}
            </div>

            {activeTab === 'menu' && (
              <div className="animate-in fade-in slide-in-from-right-4 duration-300">
                <h2 className="font-display text-3xl text-primary mb-6">
                  Menù Disponibili
                </h2>
                <div className="space-y-6">
                  {mockChef.menus.map(menu => (
                    <div
                      key={menu.id}
                      className={`p-6 rounded-2xl border-2 transition-all cursor-pointer ${
                        selectedMenu.id === menu.id
                          ? 'border-accent bg-accent/5'
                          : 'border-primary/10 bg-white hover:border-accent/50'
                      }`}
                      onClick={() => setSelectedMenu(menu)}
                    >
                      <div className="flex items-start justify-between mb-4">
                        <h3 className="font-display text-2xl text-primary">
                          {menu.name}
                        </h3>
                        <span className="text-2xl font-semibold text-accent">
                          €{menu.price}
                        </span>
                      </div>
                      <ul className="space-y-3">
                        {menu.courses.map((course, index) => (
                          <li key={index} className="flex items-start gap-3 text-foreground/80">
                            <div className="w-1.5 h-1.5 bg-accent rounded-full mt-2 flex-shrink-0" />
                            <span>{course}</span>
                          </li>
                        ))}
                      </ul>
                    </div>
                  ))}
                </div>

                <div className="mt-8 p-6 bg-muted/30 rounded-xl">
                  <p className="text-sm text-muted-foreground">
                    💡 <strong>Nota:</strong> Tutti i menù possono essere personalizzati in base alle tue esigenze e preferenze alimentari. Contatta lo chef per creare un menu su misura.
                  </p>
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
                    <span className="text-2xl font-semibold">{mockChef.rating}</span>
                    <span className="text-muted-foreground">/ 5.0</span>
                  </div>
                </div>

                <div className="space-y-6">
                  {mockChef.reviews.map((review, index) => (
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

            {activeTab === 'about' && (
              <div className="animate-in fade-in slide-in-from-right-4 duration-300">
                <h2 className="font-display text-3xl text-primary mb-6">
                  Chi Sono
                </h2>
                <p className="text-lg text-foreground/80 leading-relaxed mb-8">
                  {mockChef.bio}
                </p>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                  <div className="p-6 bg-white rounded-xl border border-primary/10">
                    <Award className="w-8 h-8 text-accent mb-3" />
                    <h3 className="font-display text-xl text-primary mb-3">Certificazioni</h3>
                    <ul className="space-y-2">
                      {mockChef.certifications.map((cert, index) => (
                        <li key={index} className="flex items-center gap-2 text-foreground/80">
                          <div className="w-1.5 h-1.5 bg-accent rounded-full" />
                          {cert}
                        </li>
                      ))}
                    </ul>
                  </div>

                  <div className="p-6 bg-white rounded-xl border border-primary/10">
                    <Clock className="w-8 h-8 text-accent mb-3" />
                    <h3 className="font-display text-xl text-primary mb-3">Esperienza</h3>
                    <p className="text-2xl font-semibold text-accent mb-2">{mockChef.experience}</p>
                    <p className="text-foreground/70">di esperienza professionale nella ristorazione di alto livello</p>
                  </div>
                </div>

                <h3 className="font-display text-2xl text-primary mb-6">Galleria</h3>
                <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                  {mockChef.gallery.map((image, index) => (
                    <div
                      key={index}
                      className="relative overflow-hidden rounded-xl aspect-square animate-in fade-in zoom-in-95 duration-300"
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
            )}
          </div>

          <div className="lg:col-span-1">
            <div className="sticky top-24 bg-white rounded-2xl border-2 border-primary/10 p-6 shadow-xl">
              <div className="text-center mb-6">
                <p className="text-muted-foreground mb-2">A partire da</p>
                <p className="font-display text-4xl text-accent mb-1">
                  €{mockChef.pricePerPerson}
                </p>
                <p className="text-sm text-muted-foreground">per persona</p>
              </div>

              <div className="space-y-4 mb-6 py-6 border-y border-primary/10">
                <div className="flex items-center gap-3">
                  <Users className="w-5 h-5 text-muted-foreground" />
                  <span className="text-sm text-foreground/80">
                    {mockChef.minGuests}-{mockChef.maxGuests} ospiti
                  </span>
                </div>
                <div className="flex items-center gap-3">
                  <MapPin className="w-5 h-5 text-muted-foreground" />
                  <span className="text-sm text-foreground/80">{mockChef.location}</span>
                </div>
                <div className="flex items-center gap-3">
                  <Calendar className="w-5 h-5 text-muted-foreground" />
                  <span className="text-sm text-foreground/80">
                    Disponibilità flessibile
                  </span>
                </div>
              </div>

              <Link
                to={`/booking/chef/${mockChef.id}`}
                className="block w-full py-4 bg-accent text-white text-center rounded-lg font-semibold hover:bg-accent/90 transition-all hover:shadow-lg hover:shadow-accent/20 mb-3"
              >
                Prenota Ora
              </Link>

              <button className="w-full py-3 border-2 border-primary/20 text-primary rounded-lg font-medium hover:bg-muted/30 transition-colors">
                Contatta Chef
              </button>

              <p className="text-xs text-muted-foreground text-center mt-4">
                Cancellazione gratuita fino a 48h prima dell'evento
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
