import { useState } from 'react';
import { User, Mail, Phone, MapPin, Camera, Save, Shield, Bell, CreditCard } from 'lucide-react';

export function Profile() {
  const [activeTab, setActiveTab] = useState<'profile' | 'security' | 'notifications' | 'billing'>('profile');
  const [isEditing, setIsEditing] = useState(false);

  const [profileData, setProfileData] = useState({
    name: 'Marco Rossi',
    email: 'marco.rossi@email.com',
    phone: '+39 339 1234567',
    location: 'Milano, Italia',
    bio: 'Appassionato di cucina gourmet e amante delle esperienze culinarie uniche.',
    role: 'client' as 'client' | 'chef' | 'manager'
  });

  const handleSave = () => {
    setIsEditing(false);
    alert('Profilo aggiornato con successo! (Simulazione)');
  };

  return (
    <div className="min-h-screen bg-background">
      <div className="bg-primary text-primary-foreground py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="font-display text-4xl md:text-5xl mb-3">
            Il Mio Profilo
          </h1>
          <p className="text-xl text-primary-foreground/80">
            Gestisci le tue informazioni personali e le impostazioni
          </p>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
          <div className="lg:col-span-1">
            <div className="bg-white rounded-xl p-6 border border-primary/10 mb-6">
              <div className="text-center mb-6">
                <div className="relative inline-block mb-4">
                  <div className="w-24 h-24 bg-accent/20 rounded-full flex items-center justify-center">
                    <span className="font-display text-3xl text-accent">
                      {profileData.name.split(' ').map(n => n[0]).join('')}
                    </span>
                  </div>
                  <button className="absolute bottom-0 right-0 w-8 h-8 bg-accent text-white rounded-full flex items-center justify-center hover:bg-accent/90 transition-colors">
                    <Camera className="w-4 h-4" />
                  </button>
                </div>
                <h2 className="font-display text-xl text-primary mb-1">
                  {profileData.name}
                </h2>
                <p className="text-sm text-muted-foreground capitalize">
                  {profileData.role === 'client' ? 'Cliente' : profileData.role === 'chef' ? 'Chef' : 'Gestore'}
                </p>
              </div>

              <div className="space-y-2">
                {[
                  { icon: User, label: 'Profilo', value: 'profile' },
                  { icon: Shield, label: 'Sicurezza', value: 'security' },
                  { icon: Bell, label: 'Notifiche', value: 'notifications' },
                  { icon: CreditCard, label: 'Pagamenti', value: 'billing' }
                ].map(item => (
                  <button
                    key={item.value}
                    onClick={() => setActiveTab(item.value as any)}
                    className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors ${
                      activeTab === item.value
                        ? 'bg-accent text-white'
                        : 'text-foreground/70 hover:bg-muted/50'
                    }`}
                  >
                    <item.icon className="w-5 h-5" />
                    <span className="font-medium">{item.label}</span>
                  </button>
                ))}
              </div>
            </div>
          </div>

          <div className="lg:col-span-3">
            {activeTab === 'profile' && (
              <div className="bg-white rounded-xl p-8 border border-primary/10 animate-in fade-in slide-in-from-right-4 duration-300">
                <div className="flex items-center justify-between mb-8">
                  <h2 className="font-display text-2xl text-primary">
                    Informazioni Personali
                  </h2>
                  {!isEditing ? (
                    <button
                      onClick={() => setIsEditing(true)}
                      className="px-6 py-2 bg-accent text-white rounded-lg font-medium hover:bg-accent/90 transition-colors"
                    >
                      Modifica
                    </button>
                  ) : (
                    <div className="flex gap-3">
                      <button
                        onClick={() => setIsEditing(false)}
                        className="px-6 py-2 border-2 border-primary/20 text-primary rounded-lg font-medium hover:bg-muted/30 transition-colors"
                      >
                        Annulla
                      </button>
                      <button
                        onClick={handleSave}
                        className="px-6 py-2 bg-accent text-white rounded-lg font-medium hover:bg-accent/90 transition-colors flex items-center gap-2"
                      >
                        <Save className="w-4 h-4" />
                        Salva
                      </button>
                    </div>
                  )}
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label className="block text-sm font-medium text-foreground mb-2">
                      <User className="w-4 h-4 inline mr-2" />
                      Nome Completo
                    </label>
                    <input
                      type="text"
                      value={profileData.name}
                      onChange={(e) => setProfileData({ ...profileData, name: e.target.value })}
                      disabled={!isEditing}
                      className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none disabled:opacity-60"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-foreground mb-2">
                      <Mail className="w-4 h-4 inline mr-2" />
                      Email
                    </label>
                    <input
                      type="email"
                      value={profileData.email}
                      onChange={(e) => setProfileData({ ...profileData, email: e.target.value })}
                      disabled={!isEditing}
                      className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none disabled:opacity-60"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-foreground mb-2">
                      <Phone className="w-4 h-4 inline mr-2" />
                      Telefono
                    </label>
                    <input
                      type="tel"
                      value={profileData.phone}
                      onChange={(e) => setProfileData({ ...profileData, phone: e.target.value })}
                      disabled={!isEditing}
                      className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none disabled:opacity-60"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-foreground mb-2">
                      <MapPin className="w-4 h-4 inline mr-2" />
                      Località
                    </label>
                    <input
                      type="text"
                      value={profileData.location}
                      onChange={(e) => setProfileData({ ...profileData, location: e.target.value })}
                      disabled={!isEditing}
                      className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none disabled:opacity-60"
                    />
                  </div>

                  <div className="md:col-span-2">
                    <label className="block text-sm font-medium text-foreground mb-2">
                      Bio
                    </label>
                    <textarea
                      value={profileData.bio}
                      onChange={(e) => setProfileData({ ...profileData, bio: e.target.value })}
                      disabled={!isEditing}
                      rows={4}
                      className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none disabled:opacity-60 resize-none"
                    />
                  </div>
                </div>
              </div>
            )}

            {activeTab === 'security' && (
              <div className="bg-white rounded-xl p-8 border border-primary/10 animate-in fade-in slide-in-from-right-4 duration-300">
                <h2 className="font-display text-2xl text-primary mb-8">
                  Sicurezza e Privacy
                </h2>

                <div className="space-y-6">
                  <div>
                    <h3 className="font-medium text-primary mb-4">Cambia Password</h3>
                    <div className="space-y-4">
                      <input
                        type="password"
                        placeholder="Password attuale"
                        className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                      />
                      <input
                        type="password"
                        placeholder="Nuova password"
                        className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                      />
                      <input
                        type="password"
                        placeholder="Conferma nuova password"
                        className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                      />
                      <button className="px-6 py-2 bg-accent text-white rounded-lg font-medium hover:bg-accent/90 transition-colors">
                        Aggiorna Password
                      </button>
                    </div>
                  </div>

                  <div className="pt-6 border-t border-primary/10">
                    <h3 className="font-medium text-primary mb-4">Autenticazione a Due Fattori</h3>
                    <div className="flex items-center justify-between p-4 bg-muted/30 rounded-lg">
                      <div>
                        <p className="font-medium text-foreground">2FA non attiva</p>
                        <p className="text-sm text-muted-foreground">Aggiungi un livello di sicurezza extra al tuo account</p>
                      </div>
                      <button className="px-6 py-2 bg-primary text-primary-foreground rounded-lg font-medium hover:bg-primary/90 transition-colors">
                        Attiva
                      </button>
                    </div>
                  </div>

                  <div className="pt-6 border-t border-primary/10">
                    <h3 className="font-medium text-primary mb-4">Sessioni Attive</h3>
                    <div className="space-y-3">
                      <div className="flex items-center justify-between p-4 bg-muted/30 rounded-lg">
                        <div>
                          <p className="font-medium text-foreground">Chrome su Windows</p>
                          <p className="text-sm text-muted-foreground">Milano, Italia • Attivo ora</p>
                        </div>
                        <span className="px-3 py-1 bg-secondary/20 text-secondary text-xs rounded-full">
                          Corrente
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {activeTab === 'notifications' && (
              <div className="bg-white rounded-xl p-8 border border-primary/10 animate-in fade-in slide-in-from-right-4 duration-300">
                <h2 className="font-display text-2xl text-primary mb-8">
                  Preferenze Notifiche
                </h2>

                <div className="space-y-6">
                  {[
                    {
                      title: 'Notifiche Email',
                      description: 'Ricevi aggiornamenti sulle prenotazioni via email',
                      enabled: true
                    },
                    {
                      title: 'Notifiche Push',
                      description: 'Ricevi notifiche push per eventi importanti',
                      enabled: true
                    },
                    {
                      title: 'Newsletter',
                      description: 'Ricevi le nostre newsletter con offerte e novità',
                      enabled: false
                    },
                    {
                      title: 'Promemoria Prenotazioni',
                      description: 'Ricevi promemoria 24h prima delle prenotazioni',
                      enabled: true
                    },
                    {
                      title: 'Messaggi Marketing',
                      description: 'Ricevi offerte personalizzate e promozioni',
                      enabled: false
                    }
                  ].map((setting, index) => (
                    <div
                      key={index}
                      className="flex items-center justify-between p-4 bg-muted/30 rounded-lg"
                    >
                      <div>
                        <p className="font-medium text-foreground">{setting.title}</p>
                        <p className="text-sm text-muted-foreground">{setting.description}</p>
                      </div>
                      <label className="relative inline-flex items-center cursor-pointer">
                        <input
                          type="checkbox"
                          defaultChecked={setting.enabled}
                          className="sr-only peer"
                        />
                        <div className="w-11 h-6 bg-muted rounded-full peer peer-checked:bg-accent transition-colors peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                      </label>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {activeTab === 'billing' && (
              <div className="bg-white rounded-xl p-8 border border-primary/10 animate-in fade-in slide-in-from-right-4 duration-300">
                <h2 className="font-display text-2xl text-primary mb-8">
                  Metodi di Pagamento
                </h2>

                <div className="space-y-6">
                  <div className="p-6 border-2 border-accent/50 bg-accent/5 rounded-xl">
                    <div className="flex items-center justify-between mb-4">
                      <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-accent/20 rounded-lg flex items-center justify-center">
                          <CreditCard className="w-6 h-6 text-accent" />
                        </div>
                        <div>
                          <p className="font-medium text-foreground">•••• •••• •••• 4242</p>
                          <p className="text-sm text-muted-foreground">Scadenza: 12/26</p>
                        </div>
                      </div>
                      <span className="px-3 py-1 bg-accent/20 text-accent text-xs rounded-full font-medium">
                        Predefinita
                      </span>
                    </div>
                  </div>

                  <button className="w-full py-3 border-2 border-dashed border-primary/20 text-primary rounded-lg font-medium hover:bg-muted/30 transition-colors">
                    + Aggiungi Metodo di Pagamento
                  </button>

                  <div className="pt-6 border-t border-primary/10">
                    <h3 className="font-medium text-primary mb-4">Storico Transazioni</h3>
                    <div className="space-y-3">
                      {[
                        { date: '15 Apr 2026', description: 'Prenotazione Chef - Elena Marchetti', amount: 1056 },
                        { date: '10 Apr 2026', description: 'Kitchen Lab Milano - 6 ore', amount: 297 },
                        { date: '5 Apr 2026', description: 'Prenotazione Chef - Takeshi Yamamoto', amount: 1980 }
                      ].map((transaction, index) => (
                        <div
                          key={index}
                          className="flex items-center justify-between p-4 bg-muted/30 rounded-lg"
                        >
                          <div>
                            <p className="font-medium text-foreground">{transaction.description}</p>
                            <p className="text-sm text-muted-foreground">{transaction.date}</p>
                          </div>
                          <p className="font-semibold text-accent">€{transaction.amount}</p>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
