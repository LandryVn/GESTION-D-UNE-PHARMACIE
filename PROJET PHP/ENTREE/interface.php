<?php
include("../MEDICAMENT/db.php");

// Récupérer toutes les entrées
$sql = "SELECT numEntree, numMedoc, Design, stockEntree, dateEntree FROM ENTREE";
$stmt = $pdo->query($sql);
$entrees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les médicaments pour le formulaire d'ajout
$sqlMeds = "SELECT numMedoc, Design FROM MEDICAMENT";
$stmtMeds = $pdo->query($sqlMeds);
$medicaments = $stmtMeds->fetchAll(PDO::FETCH_ASSOC);

// Requête pour les médicaments en rupture (pour la sidebar)
$ruptureQuery = "SELECT numMedoc, Design, prix_unitaire, stock 
                 FROM MEDICAMENT 
                 WHERE stock < 5 
                 ORDER BY stock ASC";
$ruptureStmt = $pdo->query($ruptureQuery);
$medicamentsRupture = $ruptureStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Entrées de Médicaments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --header-bg: #ffffff;  /* Changé en blanc */
            --header-text: #000000; /* Changé en noir */
            --danger-bg: #dc3545;
            --danger-text: white;
            --success-bg: #d4edda;
            --success-text: #155724;
        }
        body {
            background-color: #f8f9fa;
        }
        
        #sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            position: fixed;
            height: 100vh;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 1.5rem;
            background: #1a252f;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-link {
            padding: 1rem 1.5rem;
            color: #b8c7ce;
            display: block;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .sidebar-link:hover, .sidebar-link.active {
            color: white;
            background: rgba(0,0,0,0.2);
            border-left: 4px solid #3498db;
        }
        
        .sidebar-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        /* Main Content Styles */
        #content {
            margin-left: 250px;
            padding: 1.5rem;
            transition: all 0.3s;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            #sidebar {
                margin-left: -250px;
            }
            
            #sidebar.active {
                margin-left: 0;
            }
            
            #content {
                margin-left: 0;
            }
            
            #content.sidebar-active {
                margin-left: 250px;
                position: relative;
                overflow: hidden;
            }
        }
        
        .page-header {
            background-color: var(--header-bg);
            color: var(--header-text);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .page-header h1 {
            font-weight: 600;
        }
        
        .btn-action {
            border-radius: 50px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .rounded-table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        
        .rounded-table thead {
            background: var(--header-bg);
            color: white;
        }
        
        .rounded-table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            text-align: center;
        }
        
        .rounded-table td {
            vertical-align: middle;
            padding: 0.75rem 1rem;
            text-align: center;
        }
        
        .rounded-table tbody tr:nth-child(even) {
            background-color: rgba(0,0,0,0.02);
        }
        
        .rounded-table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border: none;
        }
        
        .modal-header {
            border-bottom: none;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .modal-header .btn-close {
            filter: invert(1);
        }
        
        .alert {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: none;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 0.5rem 1rem;
        }
        
        .form-select {
            border-radius: 8px;
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body>

<nav id="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0">
            <i class="fas fa-clinic-medical me-2"></i> PharmaSys
        </h4>
    </div>
    
    <ul class="list-unstyled px-2 pt-3">
        <li>
            <a href="../LOGIN/dashboard.php" class="sidebar-link">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="interface.php" class="sidebar-link active">
                <i class="fas fa-pills"></i> Entrée Médicaments
            </a>
        </li>
        <li>
            <a href="../ACHAT/achat.php" class="sidebar-link">
                <i class="fas fa-shopping-cart"></i> Achats
            </a>
        </li>
        <li>
            <a href="../LOGIN/rupture_stock.php" class="sidebar-link">
                <i class="fas fa-exclamation-triangle"></i> Ruptures
                <?php if(count($medicamentsRupture) > 0): ?>
                    <span class="badge bg-danger float-end"><?= count($medicamentsRupture) ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="../LOGIN/top_medicaments.php" class="sidebar-link">
                <i class="fas fa-trophy"></i> Top 5
            </a>
        </li>
        <li>
            <a href="../LOGIN/recettes_mensuelles.php" class="sidebar-link">
                <i class="fas fa-chart-bar"></i> Statistiques
            </a>
        </li>
        <br><br><br><br><br><br><br><br><br><li class="mt-4">
            <a href="../LOGIN/logout.php" class="sidebar-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </li>
    </ul>
</nav>
<button class="btn btn-dark d-lg-none m-3 position-fixed" id="sidebarToggle" style="z-index: 1050;">
    <i class="fas fa-bars"></i>
</button>

<div id="content">
    <div class="container-fluid">
        <div class="page-header bg-white text-dark"> 
            <h2 class="text-center mb-0">
                <i class="fas fa-clipboard-list me-2"></i>Gestion des Entrées de Médicaments
            </h2>
        </div>
        
        <div class="d-flex justify-content-between mb-4">
            
            <button type="button" class="btn btn-success btn-action" data-bs-toggle="modal" data-bs-target="#modalAjout">
                <i class="fas fa-plus me-2"></i>Ajouter une Entrée
            </button>
        </div>
        
        <!-- Messages d'alerte -->
        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])) : ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table rounded-table mb-0">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Médicament</th>
                                <th>Quantité</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entrees as $entree) : ?>
                                <tr>
                                    <td class="fw-bold text-center"><?= htmlspecialchars($entree['numEntree']) ?></td>
                                    <td><?= htmlspecialchars($entree['Design']) ?></td>
                                    <td><?= htmlspecialchars($entree['stockEntree']) ?></td>
                                    <td><?= htmlspecialchars($entree['dateEntree']) ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalModifier<?= $entree['numEntree'] ?>">
                                            <i class="fas fa-edit me-1"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalSupprimer<?= $entree['numEntree'] ?>">
                                            <i class="fas fa-trash me-1"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Modal Ajouter -->
    <div class="modal fade" id="modalAjout" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class=" modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Ajouter une Entrée
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../ENTREE/ajouter.php" method="post">
                        <div class="mb-3">
                            <label class="form-label">Numéro d'Entrée</label>
                            <input type="text" name="numEntree" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Médicament</label>
                            <select name="numMedoc" class="form-select" required>
                                <option value="">Sélectionnez un médicament</option>
                                <?php foreach ($medicaments as $med) : ?>
                                    <option value="<?= $med['numMedoc'] ?>"><?= $med['Design'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantité Entrée</label>
                            <input type="number" name="stockEntree" class="form-control" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date d'Entrée</label>
                            <input type="date" name="dateEntree" class="form-control" max="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-action">
                                <i class="fas fa-check me-2"></i>Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals for each row (Modifier/Supprimer) -->
    <?php foreach ($entrees as $entree) : ?>
        <!-- Modal Modifier -->
        <div class="modal fade" id="modalModifier<?= $entree['numEntree'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>Modifier l'Entrée <?= $entree['numEntree'] ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../ENTREE/modifier.php" method="post">
                            <input type="hidden" name="numEntree" value="<?= $entree['numEntree'] ?>">

                            <div class="mb-3">
                                <label class="form-label">Quantité Entrée</label>
                                <input type="number" name="stockEntree" class="form-control" min="1" value="<?= $entree['stockEntree'] ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date d'Entrée</label>
                                <input type="date" name="dateEntree" class="form-control" max="<?= date('Y-m-d') ?>" value="<?= $entree['dateEntree'] ?>" required>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-warning btn-action">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Supprimer -->
        <div class="modal fade" id="modalSupprimer<?= $entree['numEntree'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-trash me-2"></i>Supprimer l'Entrée <?= $entree['numEntree'] ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Voulez-vous vraiment supprimer cette entrée ?</p>
                        <form action="../ENTREE/supprimer.php" method="post">
                            <input type="hidden" name="numEntree" value="<?= $entree['numEntree'] ?>">
                            <div class="text-end">
                                <button type="submit" class="btn btn-danger btn-action">
                                    <i class="fas fa-check me-2"></i>Confirmer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('content').classList.toggle('sidebar-active');
        });
        
        // Close sidebar on mobile when clicking a link
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    document.getElementById('sidebar').classList.remove('active');
                    document.getElementById('content').classList.remove('sidebar-active');
                }
            });
        });
    </script>
</body>
</html>