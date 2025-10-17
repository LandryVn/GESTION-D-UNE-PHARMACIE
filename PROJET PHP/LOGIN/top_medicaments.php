<?php
require_once '../MEDICAMENT/db.php';

$topMedicaments = [];

try {
    $query = "
    SELECT 
        TRIM(Design) AS Design_clean,
        SUM(quantite) as total_vendu,
        SUM(quantite * prix_vente) as recette_totale
    FROM (
        -- Données archivées (inclut maintenant les médicaments supprimés)
        SELECT 
            TRIM(Design) AS Design,
            quantite, 
            prix_vente
        FROM STATS_VENTES
        WHERE 
            Design IS NOT NULL
            AND quantite > 0
            AND prix_vente > 0
        
        UNION ALL
        
        -- Données actuelles
        SELECT 
            TRIM(m.Design) AS Design,
            da.nbr as quantite,
            da.prix_vente
        FROM DETAIL_ACHAT da
        LEFT JOIN MEDICAMENT m ON da.numMedoc = m.numMedoc
        WHERE NOT EXISTS (
            SELECT 1 FROM STATS_VENTES sv 
            WHERE sv.numAchat = da.numAchat
        )
        AND m.Design IS NOT NULL
    ) as data_combined
    GROUP BY Design_clean
    HAVING total_vendu > 0
    ORDER BY total_vendu DESC
    LIMIT 5
";

    $stmt = $pdo->query($query);
    $topMedicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur requête Top 5: " . $e->getMessage());
    $topMedicaments = [];
}

// Requête rupture de stock 
$medicamentsRupture = $pdo->query(
    "SELECT numMedoc, Design, prix_unitaire, stock 
     FROM MEDICAMENT 
     WHERE stock < 5 
     ORDER BY stock ASC"
)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top 5 des médicaments par quantité vendue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --header-bg: #0dcaf0;
            --header-text: white;
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
        
        .card-shadow {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table-top-medicaments {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table-top-medicaments thead {
            background-color: var(--header-bg);
            color: var(--header-text);
        }
        
        .table-top-medicaments th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            text-align: center;
        }
        
        .table-top-medicaments td {
            vertical-align: middle;
            padding: 0.75rem 1rem;
            text-align: center;
        }
        
        .table-top-medicaments tbody tr:nth-child(even) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .table-top-medicaments tbody tr:hover {
            background-color: rgba(13, 202, 240, 0.05);
        }
        
        .table-top-medicaments tbody tr:first-child {
            background-color: rgba(255, 193, 7, 0.15);
        }
        
        .table-top-medicaments tbody tr:first-child td:first-child::before {
            content: "🥇";
            margin-right: 5px;
        }
        
        .table-top-medicaments tbody tr:nth-child(2) td:first-child::before {
            content: "🥈";
            margin-right: 5px;
        }
        
        .table-top-medicaments tbody tr:nth-child(3) td:first-child::before {
            content: "🥉";
            margin-right: 5px;
        }
        
        .text-revenue {
            color: #28a745;
            font-weight: bold;
        }
        
        .btn-back {
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            transform: translateX(-3px);
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
                <a href="dashboard.php" class="sidebar-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="../ENTREE/interface.php" class="sidebar-link">
                    <i class="fas fa-pills"></i> Entrée Médicaments
                </a>
            </li>
            <li>
                <a href="../ACHAT/achat.php" class="sidebar-link">
                    <i class="fas fa-shopping-cart"></i> Achats
                </a>
            </li>
            <li>
                <a href="rupture_stock.php" class="sidebar-link">
                    <i class="fas fa-exclamation-triangle"></i> Ruptures
                    <?php if(count($medicamentsRupture) > 0): ?>
                        <span class="badge bg-danger float-end"><?= count($medicamentsRupture) ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="top_medicaments.php" class="sidebar-link active">
                    <i class="fas fa-trophy"></i> Top 5
                </a>
            </li>
            <li>
                <a href="recettes_mensuelles.php" class="sidebar-link">
                    <i class="fas fa-chart-bar"></i> Statistiques
                </a>
            </li>
            <br><br><br><br><br><br><br><br><br> <li class="mt-4">
                <a href="logout.php" class="sidebar-link text-danger">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </li>
        </ul>
    </nav>
    <button class="btn btn-dark d-lg-none m-3 position-fixed" id="sidebarToggle" style="z-index: 1050;">
        <i class="fas fa-bars"></i>
    </button>

    <div id="content">
        <div class="container py-4">
            <div class="page-header text-center">
                <h1><i class="fas fa-trophy me-2"></i>Top 5 des médicaments les plus vendus (par quantité)</h1>
                <?php if(!empty($topMedicaments)): ?>
                    <span class="badge bg-white text-primary mt-2">
                        <i class="fas fa-pills me-1"></i><?= count($topMedicaments) ?> médicament(s)
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="card card-shadow">
        <div class="card-body">
            <?php if (!empty($topMedicaments)): ?>
                <div class="table-responsive">
                    <table class="table table-top-medicaments">
                        <thead>
                            <tr>
                                <th>Classement</th>
                                <th>Médicament</th>
                                <th>Quantité vendue</th>
                                <th>Recette totale</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($topMedicaments as $index => $med): ?>
<tr>
    <td class="fw-bold"><?= $index + 1 ?></td>
    <td><?= htmlspecialchars(trim($med['Design_clean'])) ?></td>
    <td><?= $med['total_vendu'] ?> unités</td>
    <td class="text-revenue">
        <?= number_format($med['recette_totale'], 0, '', ' ') ?> Ar
    </td>
</tr>
<?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-success text-center py-5">
                    <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                    <h4 class="alert-heading">Aucune donnée de vente disponible</h4>
                    <p class="mb-0">Aucun médicament n'a été vendu pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
            
        </div>
    </div>

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