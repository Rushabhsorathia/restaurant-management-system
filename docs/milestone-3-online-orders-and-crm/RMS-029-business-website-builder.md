# RMS-029: Business Website Builder

| Field | Value |
|-------|-------|
| **Ticket ID** | RMS-029 |
| **Type** | Story |
| **Epic** | CRM |
| **Milestone** | M3 - Online Orders & CRM |
| **Priority** | Low |
| **Story Points** | 8 |
| **Assignee** | Unassigned |
| **Status** | To Do |
| **Dependencies** | RMS-006 |

## User Story
As a restaurant owner, I want a professional website with my menu and online ordering, so that customers can find me online and place orders directly without aggregator commissions.

## Description
A built-in website builder creates a mobile-responsive restaurant website automatically from the POS data. It pulls the menu, images, pricing, restaurant info (address, hours, contact), and generates a one-page or multi-page site. The website supports online ordering (pickup/delivery) with direct payment, gallery, customer reviews, and SEO basics.

## Acceptance Criteria
- [ ] Auto-generate website from restaurant profile + menu data
- [ ] Template selection: 3-5 professional themes
- [ ] Sections: Hero (image + name + tagline), Menu (categorized with images), About, Gallery, Contact (map + hours), Reviews, Online Order
- [ ] Online ordering: customer selects items -> cart -> checkout -> payment -> order goes to POS
- [ ] Custom domain support (e.g., myrestaurant.com)
- [ ] SEO: meta tags, structured data (Restaurant schema), Google Maps embed
- [ ] Theme customization: colors, fonts, images, layout
- [ ] Mobile responsive (primary device = mobile)
- [ ] Social media links (Instagram, Facebook, Zomato, Swiggy)
- [ ] Analytics integration (Google Analytics)

## UI Screens

| Screen | Route | Description |
|--------|-------|-------------|
| Website Builder | /crm/website/builder | Theme + section customization |
| Website Settings | /crm/website/settings | Domain, SEO, analytics |
| Website Preview | /crm/website/preview | Live preview of website |
| Online Orders (from website) | /crm/website/orders | Orders placed via website |

### Screen Details

**Website Builder (/crm/website/builder)**
- Theme selector (cards with thumbnails): Classic, Modern, Dark, Minimal, Bold
- Customization panel:
  - Colors: Primary, Secondary, Background (color pickers with live preview)
  - Font: 5 font family options
  - Hero image upload (or select from gallery)
  - Restaurant tagline (text)
  - About text (rich text editor)
  - Gallery: upload images, drag to reorder
  - Menu sync: toggle "Auto-sync from POS menu" (default ON)
  - Show/hide sections: toggle each section on/off
  - Social media links: Instagram, Facebook, Zomato, Swiggy URLs
- Live preview (right panel, iframe of actual site)

**Website Settings (/crm/website/settings)**
- Custom domain: input field + DNS instructions
- Subdomain: auto-generated (myrestaurant.rmswebsites.com)
- SEO: Meta title, meta description, og:image
- Google Analytics ID
- Google Maps API key (for map embed)
- Business hours (per day)
- Contact: phone, email, address

**Online Order Page (customer-facing)**
- Menu categories (horizontal scroll tabs)
- Item cards: image, name, description, price, add button
- Cart: floating bottom bar with item count + total
- Checkout: name, phone, order type (pickup/delivery), address (if delivery), payment (UPI/cash on delivery)
- Order confirmation page with order number

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/v1/website | Get website config |
| PUT | /api/v1/website | Update website config |
| GET | /api/v1/website/themes | List available themes |
| POST | /api/v1/website/publish | Publish website |
| GET | /api/v1/website/preview | Get preview URL |
| POST | /api/v1/website/orders | Create order from website (public) |
| GET | /api/v1/website/orders | List website orders (staff) |
| PUT | /api/v1/website/settings | Update domain/SEO settings |

## Database Tables

```
website_configs:
  id (bigint, PK)
  outlet_id (bigint, FK)
  theme (varchar 50, default 'modern')
  primary_color (varchar 7, default '#E67E22')
  secondary_color (varchar 7, default '#2C3E50')
  font_family (varchar 50, default 'Inter')
  hero_image_path (varchar, nullable)
  tagline (varchar 200, nullable)
  about_text (text, nullable)
  custom_domain (varchar 200, nullable)
  subdomain (varchar 100, unique)
  is_published (boolean, default false)
  meta_title (varchar 200, nullable)
  meta_description (text, nullable)
  google_analytics_id (varchar 50, nullable)
  google_maps_api_key (varchar 200, nullable)
  social_links (json, nullable)
  sections_config (json) -- {"hero":true,"menu":true,"about":false,...}
  timestamps

website_gallery:
  id (bigint, PK)
  website_config_id (bigint, FK)
  image_path (varchar)
  caption (varchar 200, nullable)
  sort_order (int, default 0)
  timestamps

website_orders:
  id (bigint, PK)
  outlet_id (bigint, FK)
  customer_name (varchar 200)
  customer_phone (varchar 20)
  order_type (enum: pickup, delivery)
  delivery_address (text, nullable)
  items (json)
  subtotal (decimal 10,2)
  delivery_charge (decimal 10,2, default 0)
  total (decimal 10,2)
  payment_method (enum: upi, cash_on_delivery)
  payment_status (enum: pending, paid, failed)
  status (enum: new, confirmed, preparing, ready, delivered, cancelled)
  timestamps
```

## Technical Notes
- **Backend**: `WebsiteController.php`. Public website served via separate route group (no auth). Online orders create entries in website_orders -> auto-sync to POS order system via event.
- **Frontend**: Website is a separate React build (themed) served on subdomain/custom domain. Admin builder is part of main dashboard.

## Subtasks
1. [ ] Create website_configs, website_gallery, website_orders migrations
2. [ ] Build 3 website themes (React components)
3. [ ] Build website config API
4. [ ] Build customer-facing website (menu, cart, checkout)
5. [ ] Implement online order -> POS sync
6. [ ] Implement custom domain routing (nginx)
7. [ ] Add SEO meta tags and structured data
8. [ ] Build React website builder UI with live preview
9. [ ] Build website settings page
10. [ ] Build website orders dashboard
11. [ ] Write tests

## Testing Criteria
- [ ] Publish website -> accessible on subdomain
- [ ] Menu auto-syncs from POS -> appears on website
- [ ] Customer places order on website -> appears in POS dashboard
- [ ] Theme change -> website updates
- [ ] Mobile responsive -> renders correctly on phone
