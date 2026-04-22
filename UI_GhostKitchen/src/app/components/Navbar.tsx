import { Link, useLocation } from 'react-router';
import { ChefHat, User, Menu, X } from 'lucide-react';
import { useState } from 'react';

export function Navbar() {
  const location = useLocation();
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [currentUser] = useState<{ name: string; role: 'client' | 'chef' | 'manager' | null }>({
    name: 'Marco Rossi',
    role: 'client'
  });

  const isActive = (path: string) => {
    if (path === '/' && location.pathname === '/') return true;
    if (path !== '/' && location.pathname.startsWith(path)) return true;
    return false;
  };

  return (
    <nav className="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-primary/10">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-20">
          <Link to="/" className="flex items-center gap-3 group">
            <div className="relative">
              <ChefHat className="w-8 h-8 text-primary transition-transform group-hover:scale-110" />
              <div className="absolute inset-0 bg-accent/20 blur-xl opacity-0 group-hover:opacity-100 transition-opacity" />
            </div>
            <span className="text-2xl font-display font-semibold text-primary tracking-tight">
              Ghost Kitchen
            </span>
          </Link>

          <div className="hidden md:flex items-center gap-8">
            <Link
              to="/chefs"
              className={`text-sm font-medium transition-colors ${
                isActive('/chefs')
                  ? 'text-primary border-b-2 border-accent pb-1'
                  : 'text-foreground/70 hover:text-primary'
              }`}
            >
              Trova Chef
            </Link>
            <Link
              to="/ghost-kitchens"
              className={`text-sm font-medium transition-colors ${
                isActive('/ghost-kitchens')
                  ? 'text-primary border-b-2 border-accent pb-1'
                  : 'text-foreground/70 hover:text-primary'
              }`}
            >
              Ghost Kitchen
            </Link>
            {currentUser.role && (
              <Link
                to="/dashboard"
                className={`text-sm font-medium transition-colors ${
                  isActive('/dashboard')
                    ? 'text-primary border-b-2 border-accent pb-1'
                    : 'text-foreground/70 hover:text-primary'
                }`}
              >
                Dashboard
              </Link>
            )}
          </div>

          <div className="hidden md:flex items-center gap-4">
            {currentUser.role ? (
              <Link
                to="/profile"
                className="flex items-center gap-2 px-4 py-2 rounded-lg bg-muted/50 hover:bg-muted transition-colors"
              >
                <User className="w-4 h-4" />
                <span className="text-sm font-medium">{currentUser.name}</span>
              </Link>
            ) : (
              <>
                <button className="text-sm font-medium text-foreground/70 hover:text-primary transition-colors">
                  Accedi
                </button>
                <button className="px-5 py-2.5 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90 transition-all hover:shadow-lg">
                  Registrati
                </button>
              </>
            )}
          </div>

          <button
            className="md:hidden p-2"
            onClick={() => setIsMenuOpen(!isMenuOpen)}
          >
            {isMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
          </button>
        </div>

        {isMenuOpen && (
          <div className="md:hidden py-4 border-t border-primary/10 animate-in slide-in-from-top-2 duration-200">
            <div className="flex flex-col gap-4">
              <Link
                to="/chefs"
                className="text-sm font-medium text-foreground/70 hover:text-primary py-2"
                onClick={() => setIsMenuOpen(false)}
              >
                Trova Chef
              </Link>
              <Link
                to="/ghost-kitchens"
                className="text-sm font-medium text-foreground/70 hover:text-primary py-2"
                onClick={() => setIsMenuOpen(false)}
              >
                Ghost Kitchen
              </Link>
              {currentUser.role && (
                <Link
                  to="/dashboard"
                  className="text-sm font-medium text-foreground/70 hover:text-primary py-2"
                  onClick={() => setIsMenuOpen(false)}
                >
                  Dashboard
                </Link>
              )}
              {currentUser.role ? (
                <Link
                  to="/profile"
                  className="flex items-center gap-2 px-4 py-2 rounded-lg bg-muted/50 mt-2"
                  onClick={() => setIsMenuOpen(false)}
                >
                  <User className="w-4 h-4" />
                  <span className="text-sm font-medium">{currentUser.name}</span>
                </Link>
              ) : (
                <div className="flex flex-col gap-2 mt-2">
                  <button className="text-sm font-medium text-foreground/70 py-2">
                    Accedi
                  </button>
                  <button className="px-5 py-2.5 bg-primary text-primary-foreground rounded-lg text-sm font-medium">
                    Registrati
                  </button>
                </div>
              )}
            </div>
          </div>
        )}
      </div>
    </nav>
  );
}
