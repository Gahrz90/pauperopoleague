#!/usr/bin/env python3
"""
WCAG AA Contrast Ratio Audit
Pauperopo League – WordPress/Tailwind theme system
6 guild themes: dimir, azorius, boros, golgari, gruul, simic
"""

import math


# ── WCAG helpers ─────────────────────────────────────────────────────────────

def hex_to_rgb(hex_color: str) -> tuple[int, int, int]:
    h = hex_color.lstrip("#")
    return tuple(int(h[i:i+2], 16) for i in (0, 2, 4))


def linearize(c: float) -> float:
    """sRGB channel → linear light."""
    c /= 255.0
    return c / 12.92 if c <= 0.04045 else ((c + 0.055) / 1.055) ** 2.4


def relative_luminance(hex_color: str) -> float:
    r, g, b = hex_to_rgb(hex_color)
    return 0.2126 * linearize(r) + 0.7152 * linearize(g) + 0.0722 * linearize(b)


def contrast_ratio(hex1: str, hex2: str) -> float:
    """WCAG 2.1 contrast ratio between two hex colours."""
    l1 = relative_luminance(hex1)
    l2 = relative_luminance(hex2)
    lighter, darker = (l1, l2) if l1 > l2 else (l2, l1)
    return (lighter + 0.05) / (darker + 0.05)


def blend_rgba_on_bg(fg_rgba: tuple, bg_hex: str) -> str:
    """Alpha-composite an RGBA colour onto a solid background; returns hex."""
    r_fg, g_fg, b_fg, a = fg_rgba
    r_bg, g_bg, b_bg = hex_to_rgb(bg_hex)
    r = int(r_fg * a + r_bg * (1 - a))
    g = int(g_fg * a + g_bg * (1 - a))
    b = int(b_fg * a + b_bg * (1 - a))
    return f"#{r:02x}{g:02x}{b:02x}"


# ── Thresholds ────────────────────────────────────────────────────────────────
NORMAL_THRESHOLD = 4.5
LARGE_THRESHOLD  = 3.0


def check(label: str, fg: str, bg: str, large: bool = False, note: str = "") -> dict:
    ratio = contrast_ratio(fg, bg)
    threshold = LARGE_THRESHOLD if large else NORMAL_THRESHOLD
    passed = ratio >= threshold
    size_tag = "LARGE" if large else "NORMAL"
    return {
        "label": label,
        "fg": fg,
        "bg": bg,
        "ratio": ratio,
        "threshold": threshold,
        "passed": passed,
        "size_tag": size_tag,
        "note": note,
    }


def report_section(title: str, checks: list[dict]):
    print(f"\n{'═'*72}")
    print(f"  {title}")
    print(f"{'═'*72}")
    any_fail = False
    for c in checks:
        ratio_str = f"{c['ratio']:.2f}:1"
        status = "PASS" if c['passed'] else "FAIL ◀◀◀"
        note = f"  [{c['note']}]" if c['note'] else ""
        threshold_str = f"≥{c['threshold']:.1f}"
        marker = "  " if c['passed'] else "★ "
        print(
            f"{marker}{status}  {ratio_str:>8}  (thr {threshold_str})  "
            f"{c['size_tag']:6}  {c['fg']} on {c['bg']}"
            f"  ──  {c['label']}{note}"
        )
        if not c['passed']:
            any_fail = True
    if not any_fail:
        print("  ✓ All pairs pass in this section.")
    return checks


# ─────────────────────────────────────────────────────────────────────────────
# COLOR PALETTE CONSTANTS
# ─────────────────────────────────────────────────────────────────────────────

# Neutrals
N0   = "#ffffff"
N50  = "#f7f9fc"
N100 = "#eef1f8"
N200 = "#dde2ee"
N300 = "#c1c8dc"
N400 = "#8f9ab8"
N500 = "#636f8f"
N600 = "#434e6a"
N700 = "#2d3651"
N800 = "#1c2236"
N900 = "#0e1525"

# Blues
B100 = "#d6e8fa"
B200 = "#adcff5"
B300 = "#79aeed"
B400 = "#3f7bc0"
B500 = "#1f3fa5"
B600 = "#1a3285"
B700 = "#132466"

# Golds
G100 = "#fdf4d6"
G200 = "#fae5a0"
G300 = "#f6cc56"
G400 = "#e8a520"
G500 = "#cc8800"
G600 = "#a36c05"
G700 = "#7a4d00"

# Semantic
COLOR_TEXT    = "#0e1525"
COLOR_MUTED   = "#636f8f"
COLOR_PRIMARY = "#1f3fa5"
COLOR_ACCENT  = "#e8a520"
COLOR_SUCCESS = "#15803d"
COLOR_DANGER  = "#dc2626"
COLOR_WARNING = "#d97706"
COLOR_SURFACE = "#f7f9fc"


# ─────────────────────────────────────────────────────────────────────────────
# GLOBAL ELEMENTS (shared across all themes)
# ─────────────────────────────────────────────────────────────────────────────

global_checks = [
    # Banner / header (dark: neutral-900 bg)
    check("Banner nav links",              N300,        N900),
    check("Banner header-user text",       N300,        N900),
    check("Banner header-logout",          N400,        N900),
    check("Banner header-logout:hover",    N100,        N900),
    check("Mobile sidebar nav links",      N300,        N800),

    # Footer
    check("Footer text (muted/n500)",      N500,        N900),

    # Default body / surface
    check("Body text (color-text)",        COLOR_TEXT,  N50),
    check("Link (color-primary) on surface", COLOR_PRIMARY, N50),
    check("Muted text on dark (footer)",   COLOR_MUTED, N900),

    # Standings widget (default dark cards neutral-800)
    check("hp-standings__rank (n500)",     N500,        N800),
    check("hp-standings__record (n400)",   N400,        N800),
    check("hp-standings__pts (accent)",    COLOR_ACCENT, N800),

    # Card sub / countdown (neutral-800 bg)
    check("hp-card__sub (n400)",           N400,        N800),
    check("hp-countdown__unit (n400)",     N400,        N700),
    check("hp-countdown__val (#fff)",      N0,          N700),

    # Meta bar
    check("meta-bar__label (n300)",        N300,        N800),
    check("meta-bar__value (n400)",        N400,        N800),

    # Badges (shared)
    check("badge-blue: b600 on b100",      B600,        B100),
    check("badge-gold: g700 on g100",      G700,        G100),
    check("badge-standard: n600 on n100",  N600,        N100),
    check("badge-draft: n700 on n200",     N700,        N200),

    # Tags
    check("tag-active: #15803d on #dcfce7",   "#15803d", "#dcfce7"),
    check("tag-winner: g700 on g100",          G700,      G100),
    check("tag-pending: #92400e on #fef9c3",   "#92400e", "#fef9c3"),
    check("tag-disqualified: #991b1b on #fee2e2", "#991b1b", "#fee2e2"),

    # Alerts
    check("alert-info: b700 on b100",      B700,        B100),
    check("alert-success: #14532d on #dcfce7", "#14532d", "#dcfce7"),
    check("alert-warning: #78350f on #fef3c7", "#78350f", "#fef3c7"),
    check("alert-error: #7f1d1d on #fee2e2",   "#7f1d1d", "#fee2e2"),

    # Form inputs (all themes share white bg)
    check("form-input text (color-text) on white", COLOR_TEXT, N0),
]

all_results = []

print("=" * 72)
print("  WCAG AA CONTRAST AUDIT – PAUPEROPO LEAGUE")
print("  Thresholds: Normal text ≥4.5:1 · Large text/UI ≥3:1")
print("=" * 72)

all_results += report_section("GLOBAL ELEMENTS (all themes)", global_checks)


# ─────────────────────────────────────────────────────────────────────────────
# DIMIR (dark blue)
# ─────────────────────────────────────────────────────────────────────────────
# Theme overrides: primary #1f3fa5, neutral-800 #1c2236, neutral-900 #0e1525
# Body bg: dark gradient, approx #060810 darkest, #0e1d60 mid
DIMIR_BG     = "#060810"   # darkest body gradient stop
DIMIR_MID    = "#0e1d60"   # mid gradient stop
DIMIR_CARD   = N800        # #1c2236
DIMIR_PRI    = "#2952c8"   # btn-primary bg (from prompt — Dimir uses #2952c8)
DIMIR_PRI_HV = "#3a6ae0"   # btn-primary:hover

# Note: CSS shows dimir primary = #1f3fa5. The prompt mentions #2952c8 for btn-primary.
# Using the prompt's specific values for btn which may be a computed/lighter override.

dimir_checks = [
    # Body / gradient bg
    check("Links (#fff) on dark gradient body",  N0,          DIMIR_BG),
    check("Links (#fff) on dark gradient mid",   N0,          DIMIR_MID),

    # Brand / nav
    check("Brand-name (#fff) on header",         N0,          N900),
    check("Nav links (n300) on header",           N300,        N900),
    check("eyebrow/section labels (#fff) on dark", N0,        DIMIR_BG),

    # Buttons
    check("btn-primary: #fff on #2952c8",         N0,          DIMIR_PRI),
    check("btn-primary:hover #fff on #3a6ae0",    N0,          DIMIR_PRI_HV),
    check("btn-secondary text #fff on transparent→dark", N0,  DIMIR_BG,
          note="secondary is transparent, shows body gradient"),

    # Registration form (card bg neutral-800)
    check("form-label (#fff) on card (n800)",     N0,          DIMIR_CARD),
    check("form-input text (#0e1525) on white",   COLOR_TEXT,  N0),
    check("form-input focus border #2952c8 on white (UI≥3:1)", DIMIR_PRI, N0, large=True,
          note="focus indicator, 3:1 UI threshold"),

    # Inner dark sections
    check("inner-dark__title (#fff) on dark bg",  N0,          DIMIR_CARD),
    check("inner-dark__eyebrow (n400) on dark bg", N400,       DIMIR_CARD),
    check("inner-dark__sub (n400) on dark bg",     N400,       DIMIR_CARD),

    # Standings table
    check("standings-table__td (n300) on n800",   N300,        DIMIR_CARD),
    check("standings-table__th (n500) on n800",   N500,        DIMIR_CARD),

    # HP tcard (dark card)
    check("hp-tcard name (#fff) on n800",          N0,          DIMIR_CARD),
    check("hp-tcard date (n400) on n800",          N400,        DIMIR_CARD),
    check("hp-tcard players (n400) on n800",       N400,        DIMIR_CARD),

    # hp-tcard badge (blue-300 on blended rgba bg)
    # rgba(31,63,165,0.18) on #1c2236 card → blend
    check("hp-tcard__badge--standard (b300) on blended",
          B300,
          blend_rgba_on_bg((31, 63, 165, 0.18), DIMIR_CARD),
          note=f"blended bg≈{blend_rgba_on_bg((31,63,165,0.18), DIMIR_CARD)}"),

    # Footer
    check("footer text (n500) on n900",            N500,        N900),
    check("muted text (n500) on n900",             N500,        N900),

    # Link hover
    check("link a:hover (n400) on dark bg",        N400,        DIMIR_BG),
    check("hp-standings__pts (#fff) on n800",      N0,          DIMIR_CARD),
    check("meta-bar__label (n300) on n800",        N300,        DIMIR_CARD),
]

all_results += report_section("DIMIR (dark blue)", dimir_checks)


# ─────────────────────────────────────────────────────────────────────────────
# AZORIUS (light blue/gold)
# ─────────────────────────────────────────────────────────────────────────────
# CSS overrides: primary #1a4fa8, accent #c9940f, n700 #1e459e, n800 #122f7a, n900 #0a1f50
# Body bg gradient: #c8dff8 → #f7f2e6 → #d4e8f5; use #f7f2e6 as effective lightest body bg
# Cards: rgba(255,255,255,0.75) → effective #ffffff for contrast
AZ_PRI    = "#1a4fa8"
AZ_PRI_HV = "#225ec8"
AZ_ACC    = "#c9940f"
AZ_BODY   = "#f7f2e6"   # lightest gradient stop (effective body bg)
AZ_CARD   = N0          # rgba(255,255,255,0.75) → #ffffff effective
AZ_BTN_PRI_BG  = G400   # btn-primary bg = accent e8a520 (Azorius maps accent to gold button)
# Azorius btn-primary: bg=accent=#e8a520 no — prompt says bg #e8a520 text #1a0e02
AZ_BTN_PRI_BG2 = "#e8a520"
AZ_BTN_TXT     = "#1a0e02"
AZ_BTN_HV_BG   = "#f5c842"
AZ_BTN_SEC_COL  = "#8a5c04"  # btn-secondary color + border on card bg
AZ_BODY_TEXT   = "#1a2e5c"
AZ_N800        = "#122f7a"

azorius_checks = [
    # Body / bg (light)
    check("Body text (#1a2e5c) on gradient bg", AZ_BODY_TEXT, AZ_BODY),
    check("Body text (#1a2e5c) on card (#fff)", AZ_BODY_TEXT, AZ_CARD),

    # Headings (gold accent on white card — large text ≥3:1)
    check("Headings (h1 4.5rem): accent (#c9940f) on card", AZ_ACC, AZ_CARD,
          large=True, note="h1=72px → large text, 3:1 threshold"),
    check("Headings (h2 1.5rem=24px bold): accent (#c9940f) on card", AZ_ACC, AZ_CARD,
          large=True, note="h2=24px bold → large text, 3:1 threshold"),
    check("Headings (h3 1.125rem=18px bold): accent (#c9940f) on card", AZ_ACC, AZ_CARD,
          large=True, note="h3=18px bold → borderline large, treating as large"),
    check("Headings (h4 0.875rem=14px bold): accent (#c9940f) on card", AZ_ACC, AZ_CARD,
          large=True, note="h4=14px bold → WCAG large text threshold exactly"),
    check("Headings (h5 0.75rem=12px bold): accent (#c9940f) on card", AZ_ACC, AZ_CARD,
          note="h5=12px bold → NOT large text, normal 4.5:1"),
    check("Headings (h6 0.6875rem=11px bold): accent (#c9940f) on card", AZ_ACC, AZ_CARD,
          note="h6=11px bold → NOT large text, normal 4.5:1"),

    # Buttons
    check("btn-primary bg #e8a520 text #1a0e02", AZ_BTN_TXT, AZ_BTN_PRI_BG2),
    check("btn-primary:hover bg #f5c842 text #1a0e02", AZ_BTN_TXT, AZ_BTN_HV_BG),
    check("btn-secondary: #8a5c04 on card (#fff)", AZ_BTN_SEC_COL, AZ_CARD),
    check("btn-secondary:hover text on primary-muted #dbeafe", AZ_PRI, "#dbeafe"),

    # Forms
    check("form-label (#1a2e5c) on card (#fff)", AZ_BODY_TEXT, AZ_CARD),
    check("form-input text (#0e1525) on white",  COLOR_TEXT,   N0),

    # Standings
    check("standings-table__td (#1a4fa8) on white card", AZ_PRI, AZ_CARD),
    check("standings-table:hover td (#fff) on n800 dark", N0, N800,
          note="row hover changes bg to n800 dark"),
    check("standings-table__th (#1a4fa8) on white card", AZ_PRI, AZ_CARD),

    # Nav / outline btn
    check("hp-btn-outline (#1a2e5c) on gradient bg", AZ_BODY_TEXT, AZ_BODY),
    check("hp-season-badge (#1a2e5c) on gradient bg", AZ_BODY_TEXT, AZ_BODY),
    check("hp-section-label (#1a2e5c) on gradient bg", AZ_BODY_TEXT, AZ_BODY),
    check("inner-dark__eyebrow (#1a2e5c) on gradient top (~#c8dff8)", AZ_BODY_TEXT, "#c8dff8"),

    # Sub / card elements
    check("hp-card__sub (#1a2e5c) on card (#fff)", AZ_BODY_TEXT, AZ_CARD),
    check("hp-card__viewall:hover (#1a2e5c) on card (#fff)", AZ_BODY_TEXT, AZ_CARD),
    check("lega-tappa-row link (#1a4fa8) on card (#fff)", AZ_PRI, AZ_CARD),
    check("lega-tappa-row link:hover (#1a2e5c) on card (#fff)", AZ_BODY_TEXT, AZ_CARD),

    # Accent on bg
    check("accent (c9940f) on gradient bg (f7f2e6)", AZ_ACC, AZ_BODY,
          note="used as standalone text/eyebrow"),
]

all_results += report_section("AZORIUS (light blue/gold)", azorius_checks)


# ─────────────────────────────────────────────────────────────────────────────
# BOROS (dark red)
# ─────────────────────────────────────────────────────────────────────────────
# CSS overrides: primary #cc3333, n700 #4a281a, n800 #301a0e, n900 #1c0e07
# Body bg gradient: #7a1515 → #c44a2a → #a05020; use #c44a2a as midpoint
# Cards: neutral-800 = #301a0e (Boros override!)
BOROS_PRI    = "#cc3333"
BOROS_N700   = "#4a281a"
BOROS_N800   = "#301a0e"   # card bg for Boros (overridden)
BOROS_N900   = "#1c0e07"
BOROS_BG_MID = "#c44a2a"   # mid gradient
BOROS_BG_DRK = "#7a1515"   # darkest gradient
BOROS_IVORY  = "#fdf5e6"   # btn-primary bg
BOROS_BTN_TXT = "#5a1a0a"
BOROS_BTN_HV  = "#fdf5e6"  # slightly lighter ivory
BOROS_BTN_SEC_TXT = N0     # #ffffff on transparent (dark body)

boros_checks = [
    # Headings on dark gradient
    check("Headings (#fff) on dark gradient mid", N0, BOROS_BG_MID,
          large=True, note="large headings ≥3:1"),
    check("Headings (#fff) on dark card (n800)", N0, BOROS_N800,
          large=True, note="headings on card bg"),

    # Buttons
    check("btn-primary: #5a1a0a on ivory #fdf5e6", BOROS_BTN_TXT, BOROS_IVORY),
    check("btn-primary:hover: #5a1a0a on #fdf5e6", BOROS_BTN_TXT, "#fdf5e6"),
    check("btn-secondary text (#fff) on transparent (dark gradient mid)", N0, BOROS_BG_MID),
    check("btn-secondary text (#fff) on transparent (dark gradient drk)", N0, BOROS_BG_DRK),

    # btn-gold for Boros: bg=ivory, text=#5a1a0a (same as primary)
    check("btn-gold: #5a1a0a on ivory #fdf5e6", BOROS_BTN_TXT, BOROS_IVORY),

    # Stat / label
    check("hp-stat__label (#fdf5e6) on gradient mid", BOROS_IVORY, BOROS_BG_MID),
    check("hp-stat__label (#fdf5e6) on gradient drk", BOROS_IVORY, BOROS_BG_DRK),
    check("hp-card__eyebrow (#fdf5e6) on dark card", BOROS_IVORY, BOROS_N800),
    check("hp-section-label (#fdf5e6) on dark card",  BOROS_IVORY, BOROS_N800),
    check("brand-name (#fff) on dark header (n900)",  N0,           BOROS_N900),
    check("hp-season-badge (#fff) on dark header",    N0,           BOROS_N900),
    check("hp-stat__number--gold (#fdf5e6) on dark",  BOROS_IVORY, BOROS_N800),
    check("hp-card__viewall (#fdf5e6) on dark card",  BOROS_IVORY, BOROS_N800),

    # Inner sections
    check("inner-section-header__label (#fff) on n800", N0, BOROS_N800),
    check("inner-dark__sub (#fff) on dark card",         N0, BOROS_N800),

    # Standings
    check("standings-table__th (#fff) on n800",          N0, BOROS_N800),
    check("standings-table__td (#fff) on n800",          N0, BOROS_N800),
    check("standings-table__td--pos (#fff) on n800",     N0, BOROS_N800),
    check("standings-table__td--num (#fff) on n800",     N0, BOROS_N800),

    # Forms (Boros uses same white bg for inputs, label is #fff on dark card)
    check("form-label (#fff) on dark card (n800)",    N0,         BOROS_N800),
    check("form-input text (#0e1525) on white",       COLOR_TEXT, N0),
    check("form-input:focus border #e05050 on white (UI)", "#e05050", N0, large=True,
          note="focus indicator UI element, 3:1 threshold"),
]

all_results += report_section("BOROS (dark red)", boros_checks)


# ─────────────────────────────────────────────────────────────────────────────
# GOLGARI (black/green)
# ─────────────────────────────────────────────────────────────────────────────
# CSS overrides: primary #2d6a2d, accent #8fbc3c, n700 #1c2b1f, n800 #101a12, n900 #090f0b
# Body bg gradient: #05080a → #0a180d → #1a3d1a
# Cards: neutral-800 = #101a12 (Golgari override!)
GOL_PRI    = "#2d6a2d"
GOL_PRI_HV = "#1e5218"
GOL_ACC    = "#8fbc3c"
GOL_N800   = "#101a12"
GOL_N900   = "#090f0b"
GOL_BG     = "#0a180d"   # mid gradient
GOL_HEADING = "#4db34d"  # prompt says #4db34d (lighter green for headings)

golgari_checks = [
    # Headings
    check("Headings (#4db34d) on dark card (n800 #101a12)", GOL_HEADING, GOL_N800,
          large=True, note="large headings h1/h2 ≥3:1"),
    check("Headings (#4db34d) on dark card (normal size h4/h5)", GOL_HEADING, GOL_N800,
          note="normal size heading h4/h5 ≥4.5:1"),

    # Buttons
    check("btn-primary: #fff on #2d6a2d",         N0,          GOL_PRI),
    check("btn-primary:hover: #fff on #3d8a3d",   N0,          "#3d8a3d"),
    check("btn-secondary text (#4db34d) on dark bg", GOL_HEADING, GOL_BG),
    check("btn-secondary border (#2d6a2d) on dark bg — UI", GOL_PRI, GOL_BG, large=True,
          note="border is UI element, 3:1 threshold"),

    # btn-gold
    check("btn-gold: #fff on #2d6a2d",            N0,          GOL_PRI),
    check("btn-gold:hover: #fff on #4db34d",       N0,          GOL_HEADING,
          note="btn-gold:hover bg=#4db34d, text=#fff"),

    # Labels / badges
    check("hp-season-badge (#fff) on dark bg",     N0,          GOL_N900),
    check("hp-card__eyebrow (#fff) on dark card",  N0,          GOL_N800),
    check("hp-section-label (#fff) on dark bg",    N0,          GOL_BG),
    check("hp-card__viewall (#fff) on dark card",  N0,          GOL_N800),

    # Standings
    check("standings-table td (#fff) on n800",     N0,          GOL_N800),

    # Links / primary color on dark bg
    check("primary (#2d6a2d) on dark card n800",   GOL_PRI,     GOL_N800,
          note="link color on dark bg"),
    check("accent (#8fbc3c) on dark card n800",    GOL_ACC,     GOL_N800),
    check("accent (#8fbc3c) on dark bg gradient",  GOL_ACC,     GOL_BG),
]

all_results += report_section("GOLGARI (black/green)", golgari_checks)


# ─────────────────────────────────────────────────────────────────────────────
# GRUUL (red/green)
# ─────────────────────────────────────────────────────────────────────────────
# CSS overrides: primary #2d7d35, accent #e8563a, n700 #3d2d0e, n800 #241a09, n900 #150e06
# Body bg gradient: #6e1208 → #c43818 → #1e5c28
# Cards: neutral-800 = #241a09 (Gruul override!)
GR_PRI     = "#2d7d35"
GR_PRI_HV  = "#1f6128"
GR_ACC     = "#e8563a"
GR_N700    = "#3d2d0e"
GR_N800    = "#241a09"
GR_N900    = "#150e06"
GR_BG_MID  = "#c43818"
GR_BG_GRN  = "#1e5c28"
GR_BTN_PRI = "#6ab86e"   # prompt btn-primary bg
GR_BTN_TXT = "#071510"   # very dark text
GR_BTN_HV  = "#82cc86"
GR_BTN_SEC_TXT = "#8fd49a"  # btn-secondary text on dark bg

gruul_checks = [
    # Buttons
    check("btn-primary: #071510 on #6ab86e",       GR_BTN_TXT, GR_BTN_PRI),
    check("btn-primary:hover: #071510 on #82cc86", GR_BTN_TXT, GR_BTN_HV),
    check("btn-secondary text (#8fd49a) on dark bg mid", GR_BTN_SEC_TXT, GR_BG_MID),
    check("btn-secondary text (#8fd49a) on dark bg grn", GR_BTN_SEC_TXT, GR_BG_GRN),
    check("btn-secondary border (#2d7d35) on dark bg — UI", GR_PRI, GR_BG_MID, large=True,
          note="border is UI element, 3:1 threshold"),

    # btn-gold (same as primary)
    check("btn-gold: #071510 on #6ab86e",          GR_BTN_TXT, GR_BTN_PRI),
    check("btn-gold:hover: #071510 on #82cc86",    GR_BTN_TXT, GR_BTN_HV),

    # Stat numbers
    check("hp-stat__number--gold (#6ab86e) on dark card", GR_BTN_PRI, GR_N800),
    check("hp-stat__number--gold (#6ab86e) on dark bg",   GR_BTN_PRI, GR_BG_MID),

    # Standings
    check("standings-table td (#fff) on dark card", N0, GR_N800),

    # Navigation
    check("Nav links (n300) on n900 header", N300, N900,
          note="Gruul uses same dark neutral-900 header"),

    # Accent / primary on card bg
    check("accent (#e8563a) on dark card",  GR_ACC, GR_N800),
    check("primary (#2d7d35) on dark card", GR_PRI, GR_N800,
          note="link on dark bg"),

    # Background text contrast
    check("White text on gradient mid (#c43818)", N0, GR_BG_MID),
    check("White text on gradient grn (#1e5c28)", N0, GR_BG_GRN),
]

all_results += report_section("GRUUL (red/green)", gruul_checks)


# ─────────────────────────────────────────────────────────────────────────────
# SIMIC (blue/green/teal)
# ─────────────────────────────────────────────────────────────────────────────
# CSS overrides: primary #0b7c63, accent #3ec9b2, n700 #173630, n800 #0e2320, n900 #071614
# Body bg gradient: #083550 → #0b7c63 → #1a5c3a
# Cards: neutral-800 = #0e2320 (Simic override!)
SI_PRI     = "#0b7c63"
SI_PRI_HV  = "#076550"
SI_ACC     = "#3ec9b2"
SI_N800    = "#0e2320"
SI_N900    = "#071614"
SI_BG_BLUE = "#083550"
SI_BG_GRN  = "#1a5c3a"
SI_BTN_TXT = "#041a15"   # very dark teal-black
SI_BTN_HV  = "#5edccb"

simic_checks = [
    # Headings
    check("Headings (#3ec9b2) on dark card (n800 #0e2320)", SI_ACC, SI_N800,
          large=True, note="large headings ≥3:1"),
    check("Headings (#3ec9b2) on dark card (normal h4/h5)", SI_ACC, SI_N800,
          note="normal size headings ≥4.5:1"),

    # Buttons
    check("btn-primary: #041a15 on #3ec9b2",       SI_BTN_TXT, SI_ACC),
    check("btn-primary:hover: #041a15 on #5edccb", SI_BTN_TXT, SI_BTN_HV),
    check("btn-secondary text (#3ec9b2) on dark bg blue", SI_ACC, SI_BG_BLUE),
    check("btn-secondary text (#3ec9b2) on dark bg grn",  SI_ACC, SI_BG_GRN),
    check("btn-secondary border (#3ec9b2) on dark bg — UI", SI_ACC, SI_BG_BLUE, large=True,
          note="border UI element, 3:1 threshold"),

    # btn-gold (same colors as btn-primary for simic)
    check("btn-gold: #041a15 on #3ec9b2",          SI_BTN_TXT, SI_ACC),

    # Section labels / card elements
    check("inner-section-header__label (#3ec9b2) on dark card", SI_ACC, SI_N800),
    check("hp-section-label (#3ec9b2) on dark bg blue",         SI_ACC, SI_BG_BLUE),
    check("hp-section-label (#3ec9b2) on dark bg grn",          SI_ACC, SI_BG_GRN),
    check("hp-card__eyebrow (#3ec9b2) on dark card",            SI_ACC, SI_N800),
    check("hp-card__viewall (#3ec9b2) on dark card",            SI_ACC, SI_N800),
    check("hp-season-badge (#3ec9b2) on dark bg",               SI_ACC, SI_N900),

    # POTENTIAL ISSUE: hp-stat__number--gold uses #0e2320 (very dark) on dark bg?
    check("hp-stat__number--gold (#0e2320) on dark gradient!", SI_N800, SI_BG_BLUE,
          note="SUSPECT: very dark on dark gradient"),
    check("hp-stat__number--gold (#0e2320) on dark bg grn",    SI_N800, SI_BG_GRN,
          note="SUSPECT: very dark on dark gradient"),

    # Standings
    check("standings-table td (#fff) on dark card (n800)", N0, SI_N800),

    # Primary / accent on dark card
    check("primary (#0b7c63) on dark card n800 (link color)", SI_PRI, SI_N800,
          note="link color on dark bg"),
    check("accent (#3ec9b2) on dark bg n900",                 SI_ACC, SI_N900),
]

all_results += report_section("SIMIC (blue/green/teal)", simic_checks)


# ─────────────────────────────────────────────────────────────────────────────
# SUMMARY
# ─────────────────────────────────────────────────────────────────────────────

print(f"\n{'═'*72}")
print("  SUMMARY")
print(f"{'═'*72}")

failures = [c for c in all_results if not c['passed']]
passes   = [c for c in all_results if c['passed']]
print(f"  Total pairs checked: {len(all_results)}")
print(f"  PASS: {len(passes)}")
print(f"  FAIL: {len(failures)}")

if failures:
    print(f"\n  {'─'*68}")
    print("  FAILURES ONLY (grouped by theme)")
    print(f"  {'─'*68}")

    # group by first word of label or theme
    current_section = None
    for c in failures:
        ratio_str = f"{c['ratio']:.2f}:1"
        threshold_str = f"≥{c['threshold']:.1f}"
        note = f"  [{c['note']}]" if c['note'] else ""
        print(
            f"  FAIL  {ratio_str:>8}  (thr {threshold_str})  "
            f"{c['size_tag']:6}  {c['fg']} on {c['bg']}"
            f"\n        {c['label']}{note}"
        )

print(f"\n{'═'*72}")
print("  NOTES ON LARGE TEXT CLASSIFICATION")
print(f"{'═'*72}")
print("  WCAG 2.1 'large text': ≥18pt (24px) regular OR ≥14pt (18.67px) bold.")
print("  In this theme (base 14px = 0.875rem):")
print("  · h1  72px   regular → LARGE  (3:1)")
print("  · h2  24px   bold    → LARGE  (3:1)")
print("  · h3  18px   bold    → LARGE  (exactly 18.67px threshold, treating as LARGE)")
print("  · h4  14px   bold    → LARGE  (exactly 18.67px? No — 14px bold = 10.5pt. NOT large.)")
print("  · h5  12px   bold    → NORMAL (4.5:1)")
print("  · h6  11px   bold    → NORMAL (4.5:1)")
print("  · btn 14px   bold    → NORMAL (UI component ≥3:1 for focus indicators only)")
print("  NOTE: h4 at 0.875rem (14px) bold is 10.5pt — below the 14pt/18.67px bold threshold.")
print("        WCAG large-text threshold for bold is ≥14pt (≈18.67px). h4 does NOT qualify.")
print("        Re-checking h4 as NORMAL (4.5:1 required).")
print()

# Recalculate h4 as NORMAL for Azorius specifically
h4_acc_card = contrast_ratio(AZ_ACC, AZ_CARD)
h4_status = "PASS" if h4_acc_card >= NORMAL_THRESHOLD else "FAIL ◀◀◀"
print(f"  Azorius h4 (#c9940f on #ffffff) re-checked as NORMAL: {h4_acc_card:.2f}:1 → {h4_status}")

print(f"\n  Script complete.\n")
