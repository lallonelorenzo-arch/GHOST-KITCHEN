import { useParams, useNavigate } from 'react-router';
import { Calendar, Users, Clock, Euro, CreditCard, Check } from 'lucide-react';
import { useState } from 'react';

export function Booking() {
  const { type, id } = useParams();
  const navigate = useNavigate();
  const isChef = type === 'chef';

  const [formData, setFormData] = useState({
    date: '',
    time: '',
    guests: 4,
    hours: 3,
    specialRequests: '',
    selectedMenu: '1'
  });

  const [currentStep, setCurrentStep] = useState(1);

  const mockItem = {
    name: isChef ? 'Elena Marchetti' : 'Kitchen Lab Milano Centro',
    type: isChef ? 'Chef Mediterranea' : 'Cucina Professionale',
    image: isChef
      ? 'https://images.unsplash.com/photo-1750943082012-efe6d2fd9e45?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=400'
      : 'https://images.unsplash.com/photo-1767785990437-dfe1fe516fe8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=400',
    basePrice: isChef ? 120 : 45
  };

  const calculateTotal = () => {
    if (isChef) {
      return mockItem.basePrice * formData.guests;
    } else {
      return mockItem.basePrice * formData.hours;
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (currentStep < 3) {
      setCurrentStep(currentStep + 1);
    } else {
      alert('Prenotazione confermata! (Simulazione - backend non implementato)');
      navigate('/dashboard');
    }
  };

  const steps = ['Dettagli', 'Riepilogo', 'Pagamento'];

  return (
    <div className="min-h-screen bg-background py-12">
      <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="mb-8">
          <h1 className="font-display text-4xl md:text-5xl text-primary mb-4">
            Completa la Prenotazione
          </h1>
          <div className="flex items-center gap-4">
            {steps.map((step, index) => (
              <div key={step} className="flex items-center gap-4">
                <div className="flex items-center gap-2">
                  <div
                    className={`w-10 h-10 rounded-full flex items-center justify-center font-semibold ${
                      currentStep > index + 1
                        ? 'bg-secondary text-white'
                        : currentStep === index + 1
                        ? 'bg-accent text-white'
                        : 'bg-muted text-muted-foreground'
                    }`}
                  >
                    {currentStep > index + 1 ? <Check className="w-5 h-5" /> : index + 1}
                  </div>
                  <span
                    className={`font-medium ${
                      currentStep >= index + 1 ? 'text-primary' : 'text-muted-foreground'
                    }`}
                  >
                    {step}
                  </span>
                </div>
                {index < steps.length - 1 && (
                  <div className={`h-0.5 w-12 ${currentStep > index + 1 ? 'bg-secondary' : 'bg-muted'}`} />
                )}
              </div>
            ))}
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div className="lg:col-span-2">
            <form onSubmit={handleSubmit} className="bg-white rounded-2xl p-8 shadow-lg">
              {currentStep === 1 && (
                <div className="space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
                  <h2 className="font-display text-2xl text-primary mb-6">
                    Seleziona Data e Ora
                  </h2>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label className="block text-sm font-medium text-foreground mb-2">
                        <Calendar className="w-4 h-4 inline mr-2" />
                        Data
                      </label>
                      <input
                        type="date"
                        required
                        value={formData.date}
                        onChange={(e) => setFormData({ ...formData, date: e.target.value })}
                        min={new Date().toISOString().split('T')[0]}
                        className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-foreground mb-2">
                        <Clock className="w-4 h-4 inline mr-2" />
                        Ora
                      </label>
                      <input
                        type="time"
                        required
                        value={formData.time}
                        onChange={(e) => setFormData({ ...formData, time: e.target.value })}
                        className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                      />
                    </div>
                  </div>

                  {isChef ? (
                    <>
                      <div>
                        <label className="block text-sm font-medium text-foreground mb-2">
                          <Users className="w-4 h-4 inline mr-2" />
                          Numero di Ospiti
                        </label>
                        <input
                          type="number"
                          required
                          min="2"
                          max="20"
                          value={formData.guests}
                          onChange={(e) => setFormData({ ...formData, guests: parseInt(e.target.value) })}
                          className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                        />
                        <p className="text-sm text-muted-foreground mt-2">Minimo 2, massimo 20 ospiti</p>
                      </div>

                      <div>
                        <label className="block text-sm font-medium text-foreground mb-3">
                          Seleziona Menù
                        </label>
                        <div className="space-y-3">
                          {[
                            { id: '1', name: 'Menu Degustazione Mediterraneo', price: 120 },
                            { id: '2', name: 'Menu Vegetariano Gourmet', price: 95 },
                            { id: '3', name: 'Menu Premium Stellato', price: 180 }
                          ].map(menu => (
                            <label
                              key={menu.id}
                              className={`flex items-center justify-between p-4 rounded-lg border-2 cursor-pointer transition-colors ${
                                formData.selectedMenu === menu.id
                                  ? 'border-accent bg-accent/5'
                                  : 'border-primary/10 hover:border-accent/50'
                              }`}
                            >
                              <div className="flex items-center gap-3">
                                <input
                                  type="radio"
                                  name="menu"
                                  value={menu.id}
                                  checked={formData.selectedMenu === menu.id}
                                  onChange={(e) => setFormData({ ...formData, selectedMenu: e.target.value })}
                                  className="w-4 h-4 text-accent"
                                />
                                <span className="font-medium">{menu.name}</span>
                              </div>
                              <span className="text-accent font-semibold">€{menu.price}</span>
                            </label>
                          ))}
                        </div>
                      </div>
                    </>
                  ) : (
                    <div>
                      <label className="block text-sm font-medium text-foreground mb-2">
                        <Clock className="w-4 h-4 inline mr-2" />
                        Durata (ore)
                      </label>
                      <input
                        type="number"
                        required
                        min="3"
                        max="24"
                        value={formData.hours}
                        onChange={(e) => setFormData({ ...formData, hours: parseInt(e.target.value) })}
                        className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                      />
                      <p className="text-sm text-muted-foreground mt-2">Prenotazione minima: 3 ore</p>
                    </div>
                  )}

                  <div>
                    <label className="block text-sm font-medium text-foreground mb-2">
                      Richieste Speciali (opzionale)
                    </label>
                    <textarea
                      value={formData.specialRequests}
                      onChange={(e) => setFormData({ ...formData, specialRequests: e.target.value })}
                      rows={4}
                      placeholder={isChef ? "Es: allergie, preferenze alimentari, occasione speciale..." : "Es: attrezzature aggiuntive necessarie, note particolari..."}
                      className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none resize-none"
                    />
                  </div>
                </div>
              )}

              {currentStep === 2 && (
                <div className="space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
                  <h2 className="font-display text-2xl text-primary mb-6">
                    Riepilogo Prenotazione
                  </h2>

                  <div className="p-6 bg-muted/30 rounded-xl space-y-4">
                    <div className="flex justify-between">
                      <span className="text-muted-foreground">Data e Ora:</span>
                      <span className="font-medium">
                        {new Date(formData.date).toLocaleDateString('it-IT')} alle {formData.time}
                      </span>
                    </div>

                    {isChef ? (
                      <>
                        <div className="flex justify-between">
                          <span className="text-muted-foreground">Numero Ospiti:</span>
                          <span className="font-medium">{formData.guests} persone</span>
                        </div>
                        <div className="flex justify-between">
                          <span className="text-muted-foreground">Menu Selezionato:</span>
                          <span className="font-medium">Menu {formData.selectedMenu}</span>
                        </div>
                      </>
                    ) : (
                      <div className="flex justify-between">
                        <span className="text-muted-foreground">Durata:</span>
                        <span className="font-medium">{formData.hours} ore</span>
                      </div>
                    )}

                    {formData.specialRequests && (
                      <div className="pt-4 border-t border-primary/10">
                        <p className="text-muted-foreground mb-2">Richieste Speciali:</p>
                        <p className="text-sm text-foreground/80">{formData.specialRequests}</p>
                      </div>
                    )}
                  </div>

                  <div className="p-6 bg-accent/5 border border-accent/20 rounded-xl">
                    <h3 className="font-display text-xl text-primary mb-4">
                      Politica di Cancellazione
                    </h3>
                    <ul className="space-y-2 text-sm text-foreground/80">
                      <li className="flex items-start gap-2">
                        <Check className="w-4 h-4 text-secondary mt-0.5 flex-shrink-0" />
                        <span>Cancellazione gratuita fino a {isChef ? '48' : '24'} ore prima</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <Check className="w-4 h-4 text-secondary mt-0.5 flex-shrink-0" />
                        <span>Rimborso del 50% tra {isChef ? '48-24' : '24-12'} ore prima</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <Check className="w-4 h-4 text-secondary mt-0.5 flex-shrink-0" />
                        <span>Nessun rimborso nelle ultime {isChef ? '24' : '12'} ore</span>
                      </li>
                    </ul>
                  </div>
                </div>
              )}

              {currentStep === 3 && (
                <div className="space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
                  <h2 className="font-display text-2xl text-primary mb-6">
                    <CreditCard className="w-6 h-6 inline mr-2" />
                    Pagamento
                  </h2>

                  <div className="p-6 bg-muted/30 rounded-xl mb-6">
                    <p className="text-sm text-muted-foreground mb-4">
                      💳 <strong>Nota:</strong> Questa è una simulazione. In un ambiente di produzione, qui verrebbe integrato un sistema di pagamento sicuro (Stripe, PayPal, etc.).
                    </p>
                  </div>

                  <div className="space-y-4">
                    <div>
                      <label className="block text-sm font-medium text-foreground mb-2">
                        Numero Carta
                      </label>
                      <input
                        type="text"
                        placeholder="1234 5678 9012 3456"
                        className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                      />
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <label className="block text-sm font-medium text-foreground mb-2">
                          Scadenza
                        </label>
                        <input
                          type="text"
                          placeholder="MM/AA"
                          className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-foreground mb-2">
                          CVV
                        </label>
                        <input
                          type="text"
                          placeholder="123"
                          className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                        />
                      </div>
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-foreground mb-2">
                        Nome sul Carta
                      </label>
                      <input
                        type="text"
                        placeholder="Mario Rossi"
                        className="w-full px-4 py-3 bg-muted/30 rounded-lg border border-transparent focus:border-accent focus:outline-none"
                      />
                    </div>
                  </div>

                  <div className="flex items-start gap-3 p-4 bg-secondary/10 border border-secondary/20 rounded-lg">
                    <input type="checkbox" required className="mt-1" />
                    <p className="text-sm text-foreground/80">
                      Accetto i <a href="#" className="text-accent hover:underline">termini e condizioni</a> e la <a href="#" className="text-accent hover:underline">politica di cancellazione</a>
                    </p>
                  </div>
                </div>
              )}

              <div className="flex gap-4 mt-8 pt-6 border-t border-primary/10">
                {currentStep > 1 && (
                  <button
                    type="button"
                    onClick={() => setCurrentStep(currentStep - 1)}
                    className="flex-1 py-3 border-2 border-primary/20 text-primary rounded-lg font-medium hover:bg-muted/30 transition-colors"
                  >
                    Indietro
                  </button>
                )}
                <button
                  type="submit"
                  className="flex-1 py-3 bg-accent text-white rounded-lg font-semibold hover:bg-accent/90 transition-all hover:shadow-lg"
                >
                  {currentStep === 3 ? 'Conferma Pagamento' : 'Continua'}
                </button>
              </div>
            </form>
          </div>

          <div className="lg:col-span-1">
            <div className="sticky top-24 bg-white rounded-2xl border-2 border-primary/10 p-6 shadow-xl">
              <div className="flex items-center gap-4 mb-6 pb-6 border-b border-primary/10">
                <img
                  src={mockItem.image}
                  alt={mockItem.name}
                  className="w-20 h-20 rounded-lg object-cover"
                />
                <div>
                  <h3 className="font-display text-lg text-primary">{mockItem.name}</h3>
                  <p className="text-sm text-muted-foreground">{mockItem.type}</p>
                </div>
              </div>

              <div className="space-y-3 mb-6">
                <div className="flex justify-between text-sm">
                  <span className="text-muted-foreground">
                    {isChef ? `€${mockItem.basePrice} × ${formData.guests} ospiti` : `€${mockItem.basePrice}/h × ${formData.hours} ore`}
                  </span>
                  <span className="font-medium">€{calculateTotal()}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-muted-foreground">Commissione servizio</span>
                  <span className="font-medium">€{(calculateTotal() * 0.1).toFixed(0)}</span>
                </div>
              </div>

              <div className="pt-4 border-t-2 border-primary/20">
                <div className="flex justify-between items-baseline mb-2">
                  <span className="font-medium text-foreground">Totale</span>
                  <div className="text-right">
                    <p className="font-display text-3xl text-accent">
                      €{(calculateTotal() * 1.1).toFixed(0)}
                    </p>
                    <p className="text-xs text-muted-foreground">IVA inclusa</p>
                  </div>
                </div>
              </div>

              <div className="mt-6 p-4 bg-muted/30 rounded-lg">
                <p className="text-xs text-muted-foreground">
                  💡 Pagherai solo una caparra del 30% ora. Il saldo verrà addebitato dopo il servizio.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
