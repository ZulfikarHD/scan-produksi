# Phase 02 - Menu Management

**Sprint Goal**: Owner dapat mengelola kategori dan produk menu cafe secara lengkap (CRUD), termasuk upload gambar, pengaturan varian, dan toggle ketersediaan.

**Estimated Duration**: 4-6 days

**Depends on**: Phase 01 (Foundation)

---

## User Stories

### US-2.1: Category Management

**As a** cafe owner
**I want** to create, edit, reorder, and deactivate menu categories
**So that** my menu is organized and easy to navigate

**Acceptance Criteria:**
- [x] List all categories with sort order, active status, and product count
- [x] Create category with name and sort order
- [x] Edit category name and sort order
- [x] Toggle category active/inactive (soft-hide, not delete)
- [x] Drag-and-drop reorder (or manual sort_order input for MVP)
- [x] Validation: name required, unique, max 50 chars
- [x] Cannot delete category that has products (show warning)
- [x] Success/error toast notifications

**UI Specs:**
- Table/card list view
- Inline edit or modal form
- Active badge (green) / Inactive badge (gray)
- Product count shown per category

**Tasks:**
1. `php artisan make:controller CategoryController --resource`
2. `php artisan make:request StoreCategoryRequest`
3. `php artisan make:request UpdateCategoryRequest`
4. Create `resources/js/pages/Menu/Categories/Index.vue`
5. Create category form component (reusable for create/edit)
6. Write feature tests for CRUD operations

---

### US-2.2: Product Management - List & Create

**As a** cafe owner
**I want** to add new products to my menu
**So that** they appear on the POS screen for cashiers

**Acceptance Criteria:**
- [x] List all products with: image thumbnail, name, category, price, availability status
- [x] Filter products by category
- [x] Search products by name
- [x] Create product form with fields:
  - Name (required, max 100 chars)
  - Category (required, select from active categories)
  - Price (required, integer, min 1000)
  - Description (optional, max 255 chars)
  - Image (optional, max 2MB, jpg/png/webp)
  - Is Available (toggle, default true)
- [x] Image upload stores to `storage/app/public/products/`
- [x] Validation errors shown inline
- [x] Success redirect to product list with toast

**UI Specs:**
- Grid view (cards) with image, name, price, category badge
- Alternatively: table view with toggle
- "Tambah Produk" button (prominent, top right)
- Form as full page or large modal

**Tasks:**
1. `php artisan make:controller ProductController --resource`
2. `php artisan make:request StoreProductRequest`
3. `php artisan make:request UpdateProductRequest`
4. Create `resources/js/pages/Menu/Products/Index.vue`
5. Create `resources/js/pages/Menu/Products/Create.vue` (or modal)
6. Handle image upload + storage link
7. Write feature tests

---

### US-2.3: Product Management - Edit & Delete

**As a** cafe owner
**I want** to update product details or remove products
**So that** my menu stays accurate and up to date

**Acceptance Criteria:**
- [x] Edit all product fields (same as create)
- [x] Replace image (old image deleted from storage)
- [x] Toggle availability with single click (no full form needed)
- [x] Delete product: soft approach - only if never ordered, otherwise just mark unavailable
- [x] Confirmation dialog before delete
- [x] Bulk toggle availability (select multiple, mark available/unavailable)

**Tasks:**
1. Create `resources/js/pages/Menu/Products/Edit.vue` (or reuse form component)
2. Add quick-toggle endpoint for availability
3. Add delete with order-history check
4. Write feature tests for edit, delete, toggle

---

### US-2.4: Product Variant Management

**As a** cafe owner
**I want** to add size/type variants to products (e.g., Small/Medium/Large)
**So that** cashiers can select the correct variant and price at POS

**Acceptance Criteria:**
- [x] On product form, toggle "Has Variants" switch
- [x] When enabled: show variant list (name + price pairs)
- [x] Add variant: name (required) + price (required, integer)
- [x] Edit variant name and price inline
- [x] Delete variant (only if never ordered)
- [x] Reorder variants (sort_order)
- [x] When product has variants, base product price is display-only ("Mulai dari Rp X")
- [x] Minimum 2 variants required when has_variants is enabled
- [x] Validation: variant names unique within product

**Example Variants:**
| Product | Variant | Price |
|---------|---------|-------|
| Es Kopi Susu | Small | Rp 18.000 |
| Es Kopi Susu | Medium | Rp 22.000 |
| Es Kopi Susu | Large | Rp 28.000 |

**UI Specs:**
- Variant section appears below main product form when toggled
- Inline table with add row button
- Each row: [Name input] [Price input] [Delete button]
- Drag handle for reorder (nice-to-have)

**Tasks:**
1. Add variant management to product create/edit form
2. Create `ProductVariantController` or handle via `ProductController`
3. Nested validation in `StoreProductRequest` / `UpdateProductRequest`
4. Write feature tests for variant CRUD

---

### US-2.5: Menu Overview Page

**As a** cafe owner
**I want** a combined menu overview page
**So that** I can see my entire menu structure at a glance

**Acceptance Criteria:**
- [x] `/menu` route shows tabs or sidebar: Categories | Products
- [x] Quick stats at top: total categories, total products, unavailable products count
- [x] Easy navigation between category and product management

**Tasks:**
1. Create menu layout/index that wraps category and product pages
2. Add tab navigation or sub-menu

---

### US-2.6: Image Cover Frame, Focus & Auto-Compress

**As a** cafe owner
**I want** to frame how a photo appears on the POS card and have large photos auto-compressed
**So that** the important part of the dish shows on the 4:3 card without permanently destroying the full photo, and phone photos still upload under 2 MB

**Acceptance Criteria:**
- [x] Picking an image opens an adjust dialog (no source file size rejection)
- [x] **Product** mode = **cover frame**: full photo kept; dimmed overlay shows estimated POS card (4:3); pan to set focal point (`image_focus_x` / `image_focus_y`)
- [x] Client **auto-compress** (resize long edge + JPEG quality); **2 MB limit only after compress** — toast if still too large
- [x] Same adjust + compress UX on store logo (hard **crop** square PNG) and purchase-note photo (cover 3:4, full file kept)
- [x] POS / Menu cards use `object-cover` + `object-position` from focal point
- [x] Canceling the dialog discards the pending file
- [x] Server validation unchanged on final upload (`max:2048`)

**Out of scope:**
- Server-side image processing / Intervention Image
- Changing storage paths

**Finished notes (2026-08-13):**
- Shared `ImageCropperDialog` (`cover` | `crop`) + `compressImage.ts` + `ImageUploadField`
- Migration `image_focus_x` / `image_focus_y` on `products`
- E2E: `tests/e2e/image-crop-compress.spec.ts`

---

## Technical Notes

### Image Upload

```php
// ProductController@store
if ($request->hasFile('image')) {
    $path = $request->file('image')->store('products', 'public');
    $product->image_path = $path;
}

// Serve via: Storage::url($product->image_path)
// Ensure: php artisan storage:link
```

### Product Form Request (nested variants)

```php
class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:1000'],
            'description' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_available' => ['boolean'],
            'has_variants' => ['boolean'],
            'variants' => ['required_if:has_variants,true', 'array', 'min:2'],
            'variants.*.name' => ['required', 'string', 'max:50'],
            'variants.*.price' => ['required', 'integer', 'min:1000'],
        ];
    }
}
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|-------|-------------|----------|
| 1 | US-2.1 Category Management | 5 | Must |
| 2 | US-2.2 Product List & Create | 8 | Must |
| 3 | US-2.3 Product Edit & Delete | 5 | Must |
| 4 | US-2.4 Variant Management | 5 | Must |
| 5 | US-2.5 Menu Overview Page | 3 | Should |
| 6 | US-2.6 Image Crop & Auto-Compress | 5 | Should |

**Total Story Points: 31**

---

## Dependencies

| Dependency | From |
|-----------|------|
| Database schema (categories, products, product_variants) | Phase 01 |
| Auth & role middleware | Phase 01 |
| Base layout | Phase 01 |

## Risks

| Risk | Mitigation |
|------|-----------|
| Image upload complexity | Use Laravel's built-in storage, keep it simple |
| Variant UX too complex | Keep inline table simple, no drag for MVP |
| Form state management with nested variants | Use Inertia useForm with array handling |
