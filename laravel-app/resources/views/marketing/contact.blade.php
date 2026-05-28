<x-marketing.layout
    title="Contact and Join Waitlist"
    description="Join the NaijaBuilders waitlist for launch updates, supplier onboarding news, and iOS and Android app availability."
>
    <x-marketing.page-hero
        eyebrow="Contact"
        title="Join the NaijaBuilders waitlist."
        description="Tell us where you fit in the construction ecosystem and get ready for launch updates, supplier onboarding, and app availability."
        :image="asset('assets/images/tnx.jpg')"
    />

    <section id="waitlist" class="nb-section">
        <div class="nb-container nb-contact-layout">
            <div class="nb-contact-panel nb-glass-card nb-premium-border nb-section-reveal" data-reveal>
                <p class="nb-eyebrow">Waitlist</p>
                <h2>Get launch updates without changing any backend flow.</h2>
                <p>This form is intentionally frontend-only until a safe backend waitlist handler is added. It stores a local acknowledgement in the browser and keeps existing routes untouched.</p>
                <form class="nb-waitlist-form nb-waitlist-form-large" data-waitlist-form>
                    <label>
                        <span>Name</span>
                        <input type="text" name="name" placeholder="Your name">
                    </label>
                    <label>
                        <span>Company</span>
                        <input type="text" name="company" placeholder="Company or project name">
                    </label>
                    <label>
                        <span>Email address</span>
                        <input type="email" name="email" placeholder="you@example.com" required>
                    </label>
                    <label>
                        <span>I am interested as</span>
                        <select name="role">
                            <option>Buyer or builder</option>
                            <option>Supplier</option>
                            <option>Contractor</option>
                            <option>Service provider</option>
                            <option>Developer or project owner</option>
                        </select>
                    </label>
                    <button class="nb-btn nb-btn-primary nb-shine" type="submit">Join the Waitlist</button>
                    <p class="nb-form-note" data-waitlist-message aria-live="polite"></p>
                </form>
            </div>

            <aside class="nb-contact-aside nb-section-reveal" data-reveal>
                <div class="nb-glass-card nb-premium-border">
                    <span class="nb-card-kicker">Email</span>
                    <h3>info@naijabuilders.com</h3>
                    <p>Use the existing NaijaBuilders contact email for launch enquiries.</p>
                    <a class="nb-text-link" href="mailto:info@naijabuilders.com">Send an email</a>
                </div>
                <div class="nb-glass-card nb-premium-border">
                    <span class="nb-card-kicker">Launch updates</span>
                    <h3>Supplier onboarding soon</h3>
                    <p>Join the list if your company wants early information about materials, services, or supplier visibility.</p>
                </div>
            </aside>
        </div>
    </section>
</x-marketing.layout>
