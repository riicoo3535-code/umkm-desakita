<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasar UMKM Lokal - Direktori & Katalog Premium</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Fraunces + Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{
            --ivory: #FBF8F2;
            --ivory-deep: #F4EEE1;
            --forest: #0F3D2E;
            --forest-deep: #0A2B20;
            --gold: #C6A15B;
            --gold-soft: #E4CE9C;
            --sage: #AFC8A0;
            --ink: #1F2320;
            --stone: #6E7268;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--ivory);
            color: var(--ink);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Fraunces', serif;
            color: var(--forest-deep);
        }

        /* ---------- NAVBAR ---------- */
        .navbar {
            background-color: var(--ivory) !important;
            border-bottom: 1px solid var(--ivory-deep);
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 1px;
            font-family: 'Fraunces', serif;
            color: var(--forest-deep) !important;
        }
        .navbar-brand .text-secondary {
            color: var(--gold) !important;
        }
        .navbar-nav .nav-link {
            color: var(--stone);
            font-weight: 500;
            position: relative;
            padding-bottom: 4px;
            transition: color .2s ease;
        }
        .navbar-nav .nav-link::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 0;
            height: 2px;
            background: var(--gold);
            transition: width .25s ease;
        }
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--forest-deep);
        }
        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 100%;
        }
        .navbar-nav .nav-link[href="login.php"] {
            background: var(--forest);
            color: var(--ivory) !important;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 600;
        }
        .navbar-nav .nav-link[href="login.php"]::after {
            display: none;
        }
        .navbar-nav .nav-link[href="login.php"]:hover {
            background: var(--forest-deep);
        }

        /* ---------- HERO ---------- */
        .hero-section {
            background: radial-gradient(120% 140% at 15% 0%, #164E39 0%, var(--forest) 45%, var(--forest-deep) 100%);
            color: var(--ivory);
            padding: 100px 0;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(198, 161, 91, 0.35);
        }
        .hero-section::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 85% 20%, rgba(198,161,91,0.18) 0%, transparent 45%),
                radial-gradient(circle at 10% 90%, rgba(175,200,160,0.12) 0%, transparent 40%);
            pointer-events: none;
        }
        .hero-section .container { position: relative; z-index: 1; }

        /* ---------- CARDS ---------- */
        .card-premium {
            border: none;
            border-radius: 14px;
            background: #FFFFFF;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color .3s ease;
            box-shadow: 0 4px 6px rgba(15,61,46,0.04);
            border: 1px solid var(--ivory-deep);
        }
        .card-premium:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 30px -14px rgba(15,61,46,0.25);
            border-color: var(--gold);
        }

        /* ---------- BUTTONS ---------- */
        .btn-premium {
            background-color: var(--forest);
            color: #fff;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 600;
            border: none;
            transition: background-color .25s ease, box-shadow .25s ease;
        }
        .btn-premium:hover {
            background-color: var(--forest-deep);
            color: #fff;
            box-shadow: 0 10px 22px -10px rgba(15,61,46,0.5);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand text-uppercase text-dark" href="index.php">UMKM<span class="text-secondary">KEDUNGLO</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-3">
                <li class="nav-item"><a class="nav-link active" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="umkm.php">Daftar UMKM</a></li>
                <li class="nav-item"><a class="nav-link" href="produk.php">Produk</a></li>
                <li class="nav-item"><a class="nav-link" href="artikel.php">Artikel</a></li>
                <li class="nav-item"><a class="nav-link" href="kontak.php">Kontak</a></li>
                <a class="nav-link" href="login.php">Login Admin</a>
            </ul>
        </div>
    </div>
</nav>