<?php
require_once('../MEDICAMENT/db.php');

try {
   
    $query = "
        SELECT 
            mois_annee AS mois,
            SUM(recette) AS recette
        FROM (
            -- Données archivées
            SELECT 
                DATE_FORMAT(date_vente, '%Y-%m') AS mois_annee,
                SUM(quantite * prix_vente) AS recette
            FROM STATS_VENTES
            WHERE date_vente >= DATE_SUB(CURRENT_DATE(), INTERVAL 5 MONTH)
            GROUP BY DATE_FORMAT(date_vente, '%Y-%m')
            
            UNION ALL
            
            -- Achats actifs non archivés
            SELECT 
                DATE_FORMAT(A.dateAchat, '%Y-%m') AS mois_annee,
                SUM(DA.nbr * DA.prix_vente) AS recette
            FROM ACHAT A
            JOIN DETAIL_ACHAT DA ON A.numAchat = DA.numAchat
            WHERE A.dateAchat >= DATE_SUB(CURRENT_DATE(), INTERVAL 5 MONTH)
            AND NOT EXISTS (
                SELECT 1 FROM STATS_VENTES SV 
                WHERE SV.numAchat = A.numAchat
            )
            GROUP BY DATE_FORMAT(A.dateAchat, '%Y-%m')
        ) AS combined_data
        GROUP BY mois_annee
        ORDER BY mois_annee ASC
    ";
    
    $recetteMois = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

    
    $labels = [];
    $data = [];
    $current = new DateTime('first day of this month');
    
    for ($i = 0; $i < 5; $i++) {
        $moisKey = $current->format('Y-m');
        $moisLabel = $current->format('M Y');
        
        $trouve = false;
        foreach ($recetteMois as $row) {
            if ($row['mois'] === $moisKey) {
                $data[] = (float)$row['recette'];
                $trouve = true;
                break;
            }
        }
        
        if (!$trouve) {
            $data[] = 0;
        }
        
        $labels[] = $moisLabel;
        $current->modify('-1 month');
    }
    
    
    $labels = array_reverse($labels);
    $data = array_reverse($data);

} catch (PDOException $e) {
    error_log("Erreur recettes mensuelles: " . $e->getMessage());
    $labels = array_reverse([
        date('M Y'), 
        date('M Y', strtotime('-1 month')), 
        date('M Y', strtotime('-2 months')),
        date('M Y', strtotime('-3 months')),
        date('M Y', strtotime('-4 months'))
    ]);
    $data = [0, 0, 0, 0, 0];
}

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
    <title>Recettes mensuelles - Pharmacie</title>
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
        
        #content {
            margin-left: 250px;
            padding: 1.5rem;
            transition: all 0.3s;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
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
        
        .chart-container {
            height: 70vh;
            min-height: 500px;
        }
        
        .text-revenue {
            color: #28a745;
            font-weight: bold;
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
                <a href="top_medicaments.php" class="sidebar-link">
                    <i class="fas fa-trophy"></i> Top 5
                </a>
            </li>
            <li>
                <a href="recettes_mensuelles.php" class="sidebar-link active">
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
                <h1><i class="fas fa-chart-line me-2"></i>Recettes mensuelles</h1>
                <span class="badge bg-white text-primary mt-2">
                    <i class="fas fa-calendar-alt me-1"></i>5 derniers mois
                </span>
            </div>
            
            <div class="card card-shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Évolution des recettes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="recetteChart"></canvas>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    Mis à jour : <?= date('d/m/Y H:i') ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('recetteChart').getContext('2d');
        
        const backgroundColors = [
            'rgba(23, 162, 184, 0.7)',  // Bleu
            'rgba(40, 167, 69, 0.7)',   // Vert
            'rgba(255, 193, 7, 0.7)',   // Jaune
            'rgba(220, 53, 69, 0.7)',   // Rouge
            'rgba(108, 117, 125, 0.7)'  // Gris
        ];
        
        const borderColors = [
            'rgba(23, 162, 184, 1)',
            'rgba(40, 167, 69, 1)',
            'rgba(255, 193, 7, 1)',
            'rgba(220, 53, 69, 1)',
            'rgba(108, 117, 125, 1)'
        ];
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Recettes (Ar)',
                    data: <?= json_encode($data) ?>,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value) + ' Ar';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Recette: ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' Ar';
                            },
                            afterLabel: function(context) {
                                return 'Mois: ' + context.label;
                            }
                        }
                    }
                }
            }
        });

        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('content').classList.toggle('sidebar-active');
        });
        
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    document.getElementById('sidebar').classList.remove('active');
                    document.getElementById('content').classList.remove('sidebar-active');
                }
            });
        });
    });
    </script>
</body>
</html>