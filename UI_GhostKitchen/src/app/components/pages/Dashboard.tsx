import { useState } from 'react';
import { Calendar, TrendingUp, Users, Euro, Clock, Star, CheckCircle, XCircle, AlertCircle } from 'lucide-react';
import { BarChart, Bar, LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

const mockBookings = [
  {
    id: 1,
    clientName: 'Marco Bianchi',
    type: 'chef',
    service: 'Menu Degustazione Mediterraneo',
    date: '2026-04-20',
    time: '19:00',
    guests: 8,
    status: 'confirmed',
    total: 1056
  },
  {
    id: 2,
    clientName: 'Laura Ferri',
    type: 'kitchen',
    service: 'Kitchen Lab Milano Centro',
    date: '2026-04-18',
    time: '14:00',
    hours: 6,
    status: 'pending',
    total: 297
  },
  {
    id: 3,
    clientName: 'Andrea Romano',
    type: 'chef',
    service: 'Menu Vegetariano Gourmet',
    date: '2026-04-25',
    time: '20:00',
    guests: 12,
    status: 'confirmed',
    total: 1254
  },
  {
    id: 4,
    clientName: 'Sofia Colombo',
    type: 'kitchen',
    service: 'Pro Kitchen Roma',
    date: '2026-04-16',
    time: '09:00',
    hours: 4,
    status: 'completed',
    total: 220
  }
];

const revenueData = [
  { month: 'Ott', revenue: 3200 },
  { month: 'Nov', revenue: 4100 },
  { month: 'Dic', revenue: 5400 },
  { month: 'Gen', revenue: 4800 },
  { month: 'Feb', revenue: 5900 },
  { month: 'Mar', revenue: 6500 },
  { month: 'Apr', revenue: 4200 }
];

const bookingsData = [
  { day: 'Lun', bookings: 3 },
  { day: 'Mar', bookings: 5 },
  { day: 'Mer', bookings: 4 },
  { day: 'Gio', bookings: 7 },
  { day: 'Ven', bookings: 8 },
  { day: 'Sab', bookings: 12 },
  { day: 'Dom', bookings: 10 }
];

export function Dashboard() {
  const [activeTab, setActiveTab] = useState<'overview' | 'bookings' | 'calendar' | 'stats'>('overview');
  const [userRole] = useState<'chef' | 'manager' | 'client'>('chef');

  const stats = [
    {
      label: 'Prenotazioni Totali',
      value: '127',
      change: '+12%',
      icon: Calendar,
      color: 'bg-accent/10 text-accent'
    },
    {
      label: 'Fatturato Mese',
      value: '€6.500',
      change: '+18%',
      icon: Euro,
      color: 'bg-secondary/10 text-secondary'
    },
    {
      label: 'Valutazione Media',
      value: '4.9',
      change: '+0.2',
      icon: Star,
      color: 'bg-amber-500/10 text-amber-600'
    },
    {
      label: 'Ore Lavorate',
      value: '89',
      change: '+8%',
      icon: Clock,
      color: 'bg-blue-500/10 text-blue-600'
    }
  ];

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'confirmed':
        return (
          <span className="flex items-center gap-1 px-3 py-1 bg-secondary/20 text-secondary rounded-full text-xs font-medium">
            <CheckCircle className="w-3 h-3" />
            Confermata
          </span>
        );
      case 'pending':
        return (
          <span className="flex items-center gap-1 px-3 py-1 bg-amber-500/20 text-amber-700 rounded-full text-xs font-medium">
            <AlertCircle className="w-3 h-3" />
            In Attesa
          </span>
        );
      case 'completed':
        return (
          <span className="flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-medium">
            <CheckCircle className="w-3 h-3" />
            Completata
          </span>
        );
      case 'cancelled':
        return (
          <span className="flex items-center gap-1 px-3 py-1 bg-destructive/20 text-destructive rounded-full text-xs font-medium">
            <XCircle className="w-3 h-3" />
            Annullata
          </span>
        );
    }
  };

  return (
    <div className="min-h-screen bg-background">
      <div className="bg-primary text-primary-foreground py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="font-display text-4xl md:text-5xl mb-3">
            Dashboard
          </h1>
          <p className="text-xl text-primary-foreground/80">
            Benvenuto, {userRole === 'chef' ? 'Chef' : userRole === 'manager' ? 'Gestore' : 'Cliente'}!
          </p>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="flex gap-4 mb-8 overflow-x-auto pb-2">
          {(['overview', 'bookings', 'calendar', 'stats'] as const).map(tab => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={`px-6 py-3 rounded-lg font-medium capitalize whitespace-nowrap transition-colors ${
                activeTab === tab
                  ? 'bg-accent text-white'
                  : 'bg-white text-foreground/70 hover:bg-muted/50'
              }`}
            >
              {tab === 'overview' ? 'Panoramica' : tab === 'bookings' ? 'Prenotazioni' : tab === 'calendar' ? 'Calendario' : 'Statistiche'}
            </button>
          ))}
        </div>

        {activeTab === 'overview' && (
          <div className="space-y-8 animate-in fade-in slide-in-from-right-4 duration-300">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              {stats.map((stat, index) => (
                <div
                  key={index}
                  className="bg-white rounded-xl p-6 border border-primary/10 hover:shadow-lg transition-shadow animate-in fade-in zoom-in-95 duration-300"
                  style={{ animationDelay: `${index * 50}ms` }}
                >
                  <div className="flex items-start justify-between mb-4">
                    <div className={`w-12 h-12 rounded-lg ${stat.color} flex items-center justify-center`}>
                      <stat.icon className="w-6 h-6" />
                    </div>
                    <span className="text-secondary text-sm font-medium">{stat.change}</span>
                  </div>
                  <p className="text-3xl font-display font-semibold text-primary mb-1">
                    {stat.value}
                  </p>
                  <p className="text-sm text-muted-foreground">{stat.label}</p>
                </div>
              ))}
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <div className="bg-white rounded-xl p-6 border border-primary/10">
                <h3 className="font-display text-xl text-primary mb-6">
                  Fatturato Mensile
                </h3>
                <ResponsiveContainer width="100%" height={250}>
                  <LineChart data={revenueData}>
                    <CartesianGrid strokeDasharray="3 3" stroke="#E8E4DE" />
                    <XAxis dataKey="month" stroke="#6B5D54" />
                    <YAxis stroke="#6B5D54" />
                    <Tooltip
                      contentStyle={{
                        backgroundColor: '#FFFFFF',
                        border: '1px solid #E8E4DE',
                        borderRadius: '8px'
                      }}
                    />
                    <Line
                      type="monotone"
                      dataKey="revenue"
                      stroke="#C77152"
                      strokeWidth={3}
                      dot={{ fill: '#C77152', r: 4 }}
                    />
                  </LineChart>
                </ResponsiveContainer>
              </div>

              <div className="bg-white rounded-xl p-6 border border-primary/10">
                <h3 className="font-display text-xl text-primary mb-6">
                  Prenotazioni Settimanali
                </h3>
                <ResponsiveContainer width="100%" height={250}>
                  <BarChart data={bookingsData}>
                    <CartesianGrid strokeDasharray="3 3" stroke="#E8E4DE" />
                    <XAxis dataKey="day" stroke="#6B5D54" />
                    <YAxis stroke="#6B5D54" />
                    <Tooltip
                      contentStyle={{
                        backgroundColor: '#FFFFFF',
                        border: '1px solid #E8E4DE',
                        borderRadius: '8px'
                      }}
                    />
                    <Bar dataKey="bookings" fill="#8B9D83" radius={[8, 8, 0, 0]} />
                  </BarChart>
                </ResponsiveContainer>
              </div>
            </div>

            <div className="bg-white rounded-xl p-6 border border-primary/10">
              <div className="flex items-center justify-between mb-6">
                <h3 className="font-display text-xl text-primary">
                  Prossime Prenotazioni
                </h3>
                <button
                  onClick={() => setActiveTab('bookings')}
                  className="text-accent text-sm font-medium hover:underline"
                >
                  Vedi tutte →
                </button>
              </div>

              <div className="space-y-4">
                {mockBookings.filter(b => b.status !== 'completed').slice(0, 3).map((booking, index) => (
                  <div
                    key={booking.id}
                    className="flex items-center justify-between p-4 bg-muted/30 rounded-lg hover:bg-muted/50 transition-colors animate-in fade-in slide-in-from-left-2 duration-300"
                    style={{ animationDelay: `${index * 100}ms` }}
                  >
                    <div className="flex-1">
                      <h4 className="font-medium text-primary mb-1">{booking.clientName}</h4>
                      <p className="text-sm text-muted-foreground">{booking.service}</p>
                    </div>
                    <div className="text-right mr-6">
                      <p className="text-sm font-medium text-foreground">
                        {new Date(booking.date).toLocaleDateString('it-IT', { day: 'numeric', month: 'short' })}
                      </p>
                      <p className="text-xs text-muted-foreground">{booking.time}</p>
                    </div>
                    {getStatusBadge(booking.status)}
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}

        {activeTab === 'bookings' && (
          <div className="animate-in fade-in slide-in-from-right-4 duration-300">
            <div className="bg-white rounded-xl border border-primary/10 overflow-hidden">
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead className="bg-muted/30 border-b border-primary/10">
                    <tr>
                      <th className="px-6 py-4 text-left text-sm font-medium text-primary">Cliente</th>
                      <th className="px-6 py-4 text-left text-sm font-medium text-primary">Servizio</th>
                      <th className="px-6 py-4 text-left text-sm font-medium text-primary">Data</th>
                      <th className="px-6 py-4 text-left text-sm font-medium text-primary">Dettagli</th>
                      <th className="px-6 py-4 text-left text-sm font-medium text-primary">Totale</th>
                      <th className="px-6 py-4 text-left text-sm font-medium text-primary">Stato</th>
                      <th className="px-6 py-4 text-left text-sm font-medium text-primary">Azioni</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-primary/10">
                    {mockBookings.map(booking => (
                      <tr key={booking.id} className="hover:bg-muted/20 transition-colors">
                        <td className="px-6 py-4 text-sm text-foreground">{booking.clientName}</td>
                        <td className="px-6 py-4 text-sm text-foreground">{booking.service}</td>
                        <td className="px-6 py-4 text-sm text-foreground">
                          {new Date(booking.date).toLocaleDateString('it-IT')}
                          <br />
                          <span className="text-muted-foreground">{booking.time}</span>
                        </td>
                        <td className="px-6 py-4 text-sm text-muted-foreground">
                          {booking.type === 'chef'
                            ? `${booking.guests} ospiti`
                            : `${booking.hours} ore`
                          }
                        </td>
                        <td className="px-6 py-4 text-sm font-semibold text-accent">
                          €{booking.total}
                        </td>
                        <td className="px-6 py-4">
                          {getStatusBadge(booking.status)}
                        </td>
                        <td className="px-6 py-4">
                          <button className="text-accent text-sm font-medium hover:underline">
                            Dettagli
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'calendar' && (
          <div className="animate-in fade-in slide-in-from-right-4 duration-300">
            <div className="bg-white rounded-xl p-8 border border-primary/10 text-center">
              <Calendar className="w-16 h-16 text-muted-foreground mx-auto mb-4" />
              <h3 className="font-display text-2xl text-primary mb-3">
                Calendario Prenotazioni
              </h3>
              <p className="text-muted-foreground mb-6 max-w-md mx-auto">
                La vista calendario sarà disponibile con l'integrazione backend. Qui potrai visualizzare e gestire tutte le tue prenotazioni in formato calendario.
              </p>
              <div className="inline-block px-6 py-3 bg-muted/50 rounded-lg text-sm text-muted-foreground">
                Funzionalità in arrivo con Supabase
              </div>
            </div>
          </div>
        )}

        {activeTab === 'stats' && (
          <div className="space-y-8 animate-in fade-in slide-in-from-right-4 duration-300">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <div className="bg-white rounded-xl p-6 border border-primary/10">
                <div className="flex items-center justify-between mb-6">
                  <h3 className="font-display text-xl text-primary">
                    Performance Generale
                  </h3>
                  <TrendingUp className="w-6 h-6 text-secondary" />
                </div>

                <div className="space-y-4">
                  <div className="p-4 bg-muted/30 rounded-lg">
                    <div className="flex items-center justify-between mb-2">
                      <span className="text-sm text-muted-foreground">Tasso di Accettazione</span>
                      <span className="font-semibold text-secondary">94%</span>
                    </div>
                    <div className="w-full h-2 bg-muted rounded-full overflow-hidden">
                      <div className="h-full bg-secondary rounded-full" style={{ width: '94%' }} />
                    </div>
                  </div>

                  <div className="p-4 bg-muted/30 rounded-lg">
                    <div className="flex items-center justify-between mb-2">
                      <span className="text-sm text-muted-foreground">Tasso di Completamento</span>
                      <span className="font-semibold text-accent">98%</span>
                    </div>
                    <div className="w-full h-2 bg-muted rounded-full overflow-hidden">
                      <div className="h-full bg-accent rounded-full" style={{ width: '98%' }} />
                    </div>
                  </div>

                  <div className="p-4 bg-muted/30 rounded-lg">
                    <div className="flex items-center justify-between mb-2">
                      <span className="text-sm text-muted-foreground">Soddisfazione Cliente</span>
                      <span className="font-semibold text-amber-600">96%</span>
                    </div>
                    <div className="w-full h-2 bg-muted rounded-full overflow-hidden">
                      <div className="h-full bg-amber-500 rounded-full" style={{ width: '96%' }} />
                    </div>
                  </div>
                </div>
              </div>

              <div className="bg-white rounded-xl p-6 border border-primary/10">
                <h3 className="font-display text-xl text-primary mb-6">
                  Top Clienti
                </h3>
                <div className="space-y-4">
                  {[
                    { name: 'Marco Bianchi', bookings: 12, spent: 2340 },
                    { name: 'Laura Ferri', bookings: 8, spent: 1680 },
                    { name: 'Andrea Romano', bookings: 7, spent: 1470 },
                    { name: 'Sofia Colombo', bookings: 6, spent: 1260 }
                  ].map((client, index) => (
                    <div
                      key={index}
                      className="flex items-center justify-between p-4 bg-muted/30 rounded-lg"
                    >
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 bg-accent/20 rounded-full flex items-center justify-center font-display font-semibold text-accent">
                          {client.name.split(' ').map(n => n[0]).join('')}
                        </div>
                        <div>
                          <p className="font-medium text-primary">{client.name}</p>
                          <p className="text-sm text-muted-foreground">{client.bookings} prenotazioni</p>
                        </div>
                      </div>
                      <p className="font-semibold text-accent">€{client.spent}</p>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
