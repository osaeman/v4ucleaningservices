# V4U Cleaning Services – Updated Website Source

This package contains the updated V4U Cleaning Services single-page website with separated HTML, CSS, JavaScript, and the existing PHP SMTP mail handler.

## File Structure

```txt
v4u-cleaning-updated/
├── index.html
├── .htaccess
├── assests/
│   └── optimized/       # Responsive WebP images
├── css/
│   └── style.css
├── js/
│   └── script.js
├── mailer.php
└── README.md
```

## Updates Included

- Reduced the image payload from approximately 13.4 MB of JPEGs to approximately 1.3 MB of responsive WebP variants.
- Added mobile-appropriate `srcset` and `sizes` values so phones do not download desktop-scale service images.
- Preloaded the first hero image and delayed the remaining carousel images until after the initial page load.
- Deferred the quote-section background image until the section approaches the viewport.
- Added explicit image dimensions and asynchronous decoding to reduce layout shifts and main-thread work.
- Added Apache compression and browser-caching rules in `.htaccess`.

- Separated inline CSS and JavaScript into dedicated files.
- Modernized the header CTA, hero buttons, and quote submit button.
- Improved hero carousel overlay for stronger text readability on the left and lighter image visibility on the right.
- Fixed the 768px issue where the hero statistics section was hidden.
- Redesigned carousel indicators for better visibility and proportion.
- Converted Services and About sections to clean white backgrounds with adjusted text/card colors.
- Removed the About section quote button and shortened the About text.
- Redesigned the quote form section while keeping the existing form functionality and `mailer.php` submission intact.
- Removed the extra consent statement from the quote area.
- Reduced input placeholder font size.
- Redesigned the footer as a minimal layout with the correct office address and operating-time note.

## Form Delivery

The quote form still submits to:

```txt
mailer.php
```

The PHP handler sends enquiries to:

```txt
info@v4ucleaningservices.co.uk
```

Before going live, update the SMTP password and host details in `mailer.php`.
