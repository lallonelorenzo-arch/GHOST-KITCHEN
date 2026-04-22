import { Link } from 'react-router';
import { Search, ChefHat, Calendar, Star, MapPin, ArrowRight } from 'lucide-react';
import { ImageWithFallback } from '../figma/ImageWithFallback';

const featuredChefs = [
  {
    id: 1,
    name: 'Elena Marchetti',
    specialty: 'Cucina Mediterranea',
    rating: 4.9,
    reviews: 127,
    image: 'https://images.unsplash.com/photo-1750943082012-efe6d2fd9e45?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    location: 'Milano',
    priceRange: '€€€'
  },
  {
    id: 2,
    name: 'Takeshi Yamamoto',
    specialty: 'Cucina Giapponese',
    rating: 5.0,
    reviews: 89,
    image: 'https://images.unsplash.com/photo-1764397514739-57a0ac81330d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    location: 'Roma',
    priceRange: '€€€€'
  },
  {
    id: 3,
    name: 'Maria Santoro',
    specialty: 'Cucina Vegana',
    rating: 4.8,
    reviews: 156,
    image: 'https://images.unsplash.com/photo-1750943079478-ae516c4133cd?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    location: 'Firenze',
    priceRange: '€€'
  },
  {
    id: 4,
    name: 'Pierre Dubois',
    specialty: 'Haute Cuisine',
    rating: 4.9,
    reviews: 203,
    image: 'https://images.unsplash.com/photo-1759741558258-f24a3e847968?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800',
    location: 'Torino',
    priceRange: '€€€€'
  }
];

const cuisineTypes = [
  { name: 'Italiana', count: 142 },
  { name: 'Giapponese', count: 87 },
  { name: 'Vegana', count: 94 },
  { name: 'Mediterranea', count: 118 },
  { name: 'Fusion', count: 76 },
  { name: 'Pasticceria', count: 65 }
];

export function Home() {
  return (
    <div className="min-h-screen">
      <section className="relative h-[90vh] flex items-center overflow-hidden">
        <div className="absolute inset-0 z-0">
          <img
            src="https://images.unsplash.com/photo-1750943081248-833d71a2ab8e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1920"
            alt="Hero"
            className="w-full h-full object-cover"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-primary/90 via-primary/70 to-transparent" />
        </div>

        <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
          <div className="max-w-2xl">
            <h1 className="font-display text-6xl md:text-7xl lg:text-8xl text-white mb-6 leading-none animate-in fade-in slide-in-from-left-8 duration-700">
              L'arte culinaria
              <span className="block text-accent mt-2">a casa tua</span>
            </h1>
            <p className="text-xl text-white/90 mb-10 leading-relaxed animate-in fade-in slide-in-from-left-8 duration-700 delay-100">
              Prenota chef professionisti per eventi esclusivi o affitta una ghost kitchen per le tue creazioni culinarie
            </p>

            <div className="flex flex-col sm:flex-row gap-4 animate-in fade-in slide-in-from-left-8 duration-700 delay-200">
              <Link
                to="/chefs"
                className="group px-8 py-4 bg-accent text-white rounded-lg font-medium hover:bg-accent/90 transition-all hover:shadow-2xl hover:shadow-accent/20 flex items-center justify-center gap-2"
              >
                <Search className="w-5 h-5" />
                Trova uno Chef
                <ArrowRight className="w-5 h-5 group-hover:translate-x-1 transition-transform" />
              </Link>
              <Link
                to="/ghost-kitchens"
                className="px-8 py-4 bg-white/10 backdrop-blur-sm text-white border-2 border-white/30 rounded-lg font-medium hover:bg-white/20 transition-all flex items-center justify-center gap-2"
              >
                <ChefHat className="w-5 h-5" />
                Esplora Ghost Kitchen
              </Link>
            </div>
          </div>
        </div>

        <div className="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
          <div className="w-6 h-10 border-2 border-white/50 rounded-full flex items-start justify-center p-2">
            <div className="w-1.5 h-1.5 bg-white/50 rounded-full" />
          </div>
        </div>
      </section>

      <section className="py-24 bg-background">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between mb-12">
            <div>
              <h2 className="font-display text-4xl md:text-5xl text-primary mb-3">
                Chef in Evidenza
              </h2>
              <p className="text-muted-foreground text-lg">
                I migliori chef professionisti selezionati per te
              </p>
            </div>
            <Link
              to="/chefs"
              className="hidden md:flex items-center gap-2 text-accent font-medium hover:gap-3 transition-all"
            >
              Vedi tutti
              <ArrowRight className="w-5 h-5" />
            </Link>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {featuredChefs.map((chef, index) => (
              <Link
                key={chef.id}
                to={`/chefs/${chef.id}`}
                className="group animate-in fade-in slide-in-from-bottom-4 duration-500"
                style={{ animationDelay: `${index * 100}ms` }}
              >
                <div className="relative overflow-hidden rounded-2xl aspect-[3/4] mb-4">
                  <img
                    src={chef.image}
                    alt={chef.name}
                    className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-primary/80 via-primary/20 to-transparent opacity-60 group-hover:opacity-80 transition-opacity" />
                  <div className="absolute bottom-0 left-0 right-0 p-6 text-white">
                    <div className="flex items-center gap-2 mb-2">
                      <Star className="w-4 h-4 fill-accent text-accent" />
                      <span className="font-semibold">{chef.rating}</span>
                      <span className="text-white/80 text-sm">({chef.reviews})</span>
                    </div>
                  </div>
                  <div className="absolute top-4 right-4 px-3 py-1 bg-white/95 backdrop-blur-sm rounded-full text-xs font-medium text-primary">
                    {chef.priceRange}
                  </div>
                </div>
                <h3 className="font-display text-2xl text-primary mb-1 group-hover:text-accent transition-colors">
                  {chef.name}
                </h3>
                <p className="text-muted-foreground mb-2">{chef.specialty}</p>
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                  <MapPin className="w-4 h-4" />
                  {chef.location}
                </div>
              </Link>
            ))}
          </div>

          <Link
            to="/chefs"
            className="md:hidden flex items-center justify-center gap-2 text-accent font-medium mt-8 hover:gap-3 transition-all"
          >
            Vedi tutti gli chef
            <ArrowRight className="w-5 h-5" />
          </Link>
        </div>
      </section>

      <section className="py-24 bg-muted/30">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="font-display text-4xl md:text-5xl text-primary mb-4">
              Esplora per Cucina
            </h2>
            <p className="text-muted-foreground text-lg max-w-2xl mx-auto">
              Trova lo chef perfetto in base alle tue preferenze culinarie
            </p>
          </div>

          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {cuisineTypes.map((cuisine, index) => (
              <Link
                key={cuisine.name}
                to={`/chefs?cuisine=${cuisine.name.toLowerCase()}`}
                className="group p-6 bg-white rounded-xl border border-primary/10 hover:border-accent hover:shadow-lg transition-all animate-in fade-in zoom-in-95 duration-300"
                style={{ animationDelay: `${index * 50}ms` }}
              >
                <h4 className="font-display text-xl text-primary mb-2 group-hover:text-accent transition-colors">
                  {cuisine.name}
                </h4>
                <p className="text-sm text-muted-foreground">
                  {cuisine.count} chef
                </p>
              </Link>
            ))}
          </div>
        </div>
      </section>

      <section className="py-24 bg-primary text-primary-foreground overflow-hidden relative">
        <div className="absolute inset-0 opacity-5">
          <div className="absolute inset-0" style={{
            backgroundImage: 'radial-gradient(circle at 2px 2px, currentColor 1px, transparent 0)',
            backgroundSize: '40px 40px'
          }} />
        </div>

        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div>
              <h2 className="font-display text-4xl md:text-5xl mb-6">
                Ghost Kitchen Professionali
              </h2>
              <p className="text-xl text-primary-foreground/80 mb-8 leading-relaxed">
                Affitta cucine professionali completamente attrezzate per ore o giornate. Perfette per chef, catering e progetti culinari.
              </p>
              <ul className="space-y-4 mb-10">
                {[
                  'Attrezzature professionali certificate',
                  'Disponibilità flessibile a fasce orarie',
                  'Igiene e sicurezza garantite',
                  'Posizioni strategiche in tutta Italia'
                ].map((feature, index) => (
                  <li key={index} className="flex items-center gap-3">
                    <div className="w-2 h-2 bg-accent rounded-full" />
                    <span className="text-lg">{feature}</span>
                  </li>
                ))}
              </ul>
              <Link
                to="/ghost-kitchens"
                className="inline-flex items-center gap-2 px-8 py-4 bg-accent text-white rounded-lg font-medium hover:bg-accent/90 transition-all hover:shadow-2xl hover:shadow-accent/20"
              >
                Scopri le Ghost Kitchen
                <ArrowRight className="w-5 h-5" />
              </Link>
            </div>

            <div className="relative">
              <div className="relative rounded-2xl overflow-hidden shadow-2xl">
                <img
                  src="https://images.unsplash.com/photo-1767785990437-dfe1fe516fe8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1200"
                  alt="Ghost Kitchen"
                  className="w-full h-full object-cover"
                />
              </div>
              <div className="absolute -bottom-6 -left-6 w-32 h-32 bg-accent rounded-full blur-3xl opacity-30" />
              <div className="absolute -top-6 -right-6 w-40 h-40 bg-secondary rounded-full blur-3xl opacity-20" />
            </div>
          </div>
        </div>
      </section>

      <section className="py-24 bg-background">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="font-display text-4xl md:text-5xl text-primary mb-4">
              Come Funziona
            </h2>
            <p className="text-muted-foreground text-lg max-w-2xl mx-auto">
              Prenotare uno chef o una ghost kitchen è semplice e veloce
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-12">
            {[
              {
                icon: Search,
                title: 'Cerca e Scopri',
                description: 'Esplora profili di chef e ghost kitchen, leggi recensioni e confronta prezzi'
              },
              {
                icon: Calendar,
                title: 'Prenota',
                description: 'Scegli data e orario, personalizza il menu o seleziona gli equipaggiamenti necessari'
              },
              {
                icon: ChefHat,
                title: 'Goditi l\'Esperienza',
                description: 'Rilassati mentre lo chef prepara o inizia a creare nella tua ghost kitchen'
              }
            ].map((step, index) => (
              <div
                key={index}
                className="text-center animate-in fade-in slide-in-from-bottom-4 duration-500"
                style={{ animationDelay: `${index * 150}ms` }}
              >
                <div className="inline-flex items-center justify-center w-20 h-20 bg-accent/10 rounded-full mb-6 relative">
                  <step.icon className="w-10 h-10 text-accent" />
                  <div className="absolute -top-2 -right-2 w-8 h-8 bg-accent text-white rounded-full flex items-center justify-center font-display font-semibold">
                    {index + 1}
                  </div>
                </div>
                <h3 className="font-display text-2xl text-primary mb-3">
                  {step.title}
                </h3>
                <p className="text-muted-foreground leading-relaxed">
                  {step.description}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
}
