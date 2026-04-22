import { Link } from 'react-router';
import { Home, Search, ChefHat } from 'lucide-react';

export function NotFound() {
  return (
    <div className="min-h-screen bg-background flex items-center justify-center px-4">
      <div className="max-w-2xl mx-auto text-center">
        <div className="mb-8 animate-in zoom-in-50 duration-500">
          <div className="relative inline-block">
            <h1 className="font-display text-[150px] md:text-[200px] text-primary/10 leading-none">
              404
            </h1>
            <div className="absolute inset-0 flex items-center justify-center">
              <ChefHat className="w-24 h-24 md:w-32 md:h-32 text-accent animate-in spin-in-180 duration-700 delay-200" />
            </div>
          </div>
        </div>

        <div className="animate-in fade-in slide-in-from-bottom-4 duration-500 delay-300">
          <h2 className="font-display text-4xl md:text-5xl text-primary mb-4">
            Pagina Non Trovata
          </h2>
          <p className="text-xl text-muted-foreground mb-8 max-w-md mx-auto leading-relaxed">
            Sembra che questa ricetta non sia nel nostro menu. Torna alla homepage o esplora le altre sezioni.
          </p>

          <div className="flex flex-col sm:flex-row gap-4 justify-center animate-in fade-in slide-in-from-bottom-4 duration-500 delay-500">
            <Link
              to="/"
              className="inline-flex items-center justify-center gap-2 px-8 py-4 bg-accent text-white rounded-lg font-semibold hover:bg-accent/90 transition-all hover:shadow-lg hover:shadow-accent/20"
            >
              <Home className="w-5 h-5" />
              Torna alla Home
            </Link>
            <Link
              to="/chefs"
              className="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white border-2 border-primary/20 text-primary rounded-lg font-semibold hover:bg-muted/30 transition-colors"
            >
              <Search className="w-5 h-5" />
              Cerca Chef
            </Link>
          </div>
        </div>

        <div className="mt-16 grid grid-cols-3 gap-6 animate-in fade-in duration-500 delay-700">
          <Link
            to="/chefs"
            className="p-6 bg-white rounded-xl border border-primary/10 hover:border-accent hover:shadow-lg transition-all group"
          >
            <ChefHat className="w-8 h-8 text-accent mx-auto mb-3 group-hover:scale-110 transition-transform" />
            <p className="text-sm font-medium text-primary">Chef</p>
          </Link>
          <Link
            to="/ghost-kitchens"
            className="p-6 bg-white rounded-xl border border-primary/10 hover:border-accent hover:shadow-lg transition-all group"
          >
            <div className="w-8 h-8 text-accent mx-auto mb-3 flex items-center justify-center font-display text-2xl group-hover:scale-110 transition-transform">
              🏠
            </div>
            <p className="text-sm font-medium text-primary">Cucine</p>
          </Link>
          <Link
            to="/dashboard"
            className="p-6 bg-white rounded-xl border border-primary/10 hover:border-accent hover:shadow-lg transition-all group"
          >
            <div className="w-8 h-8 text-accent mx-auto mb-3 flex items-center justify-center font-display text-2xl group-hover:scale-110 transition-transform">
              📊
            </div>
            <p className="text-sm font-medium text-primary">Dashboard</p>
          </Link>
        </div>
      </div>
    </div>
  );
}
