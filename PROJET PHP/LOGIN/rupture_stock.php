<?php
require_once '../MEDICAMENT/db.php'; 

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruptures de stock - PharmaSys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --header-bg: #e74c3c; 
            --header-text: white;
            --danger-bg: #e74c3c;
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
        
        .badge-rupture {
            font-size: 1rem;
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 600;
            background-color: white;
            color: var(--header-bg);
        }
        
        .card-shadow {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table-rupture {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table-rupture thead {
            background-color: var(--header-bg);
            color: var(--header-text);
        }
        
        .table-rupture th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            text-align: center;
        }
        
        .table-rupture td {
            vertical-align: middle;
            padding: 0.75rem 1rem;
            text-align: center;
        }
        
        .table-rupture tbody tr:nth-child(even) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .table-rupture tbody tr:hover {
            background-color: rgba(231, 76, 60, 0.05);
        }
        
        .stock-danger {
            font-weight: 700;
            color: var(--danger-bg);
            background-color: rgba(231, 76, 60, 0.1);
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        
        .btn-back {
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            transform: translateX(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .alert-success {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            <a href="rupture_stock.php" class="sidebar-link active">
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
            <a href="recettes_mensuelles.php" class="sidebar-link">
                <i class="fas fa-chart-bar"></i> Statistiques
            </a>
        </li>
        <br><br><br><br><br><br><br><br><br><li class="mt-4">
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
            <h1><i class="fas fa-exclamation-triangle me-2"></i>Médicaments en rupture de stock (stock < 5)</h1>
            <?php if(count($medicamentsRupture) > 0): ?>
                <span class="badge-rupture mt-2">
                    <i class="fas fa-pills me-1"></i><?= count($medicamentsRupture) ?> médicament(s)
                </span>
            <?php endif; ?>
        </div>
        <div class="card card-shadow">
            <div class="card-body p-0">
                <?php if(count($medicamentsRupture) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-rupture">
                            <thead>
                                <tr>
                                    <th class="text-center">Code</th>
                                    <th>Désignation</th>
                                    <th class="text-center">Prix Unitaire</th>
                                    <th class="text-center">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($medicamentsRupture as $med): ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?= htmlspecialchars($med['numMedoc']) ?></td>
                                        <td><?= htmlspecialchars($med['Design']) ?></td>
                                        <td class="text-center"><?= number_format($med['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                                        <td class="text-center">
                                            <span class="stock-danger"><?= htmlspecialchars($med['stock']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success text-center py-5 m-4">
                        <div class="mb-3">
                            <i class="fas fa-check-circle" style="font-size: 2.5rem;"></i>
                        </div>
                        <h4 class="alert-heading mb-3">Aucun médicament en rupture de stock !</h4>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
   
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
</script>

</body>
</html>