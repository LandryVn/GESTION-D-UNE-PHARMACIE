<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=pharmacie", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC 
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}


$query = $pdo->query("SELECT * FROM MEDICAMENT");
$medicaments = $query->fetchAll();


if (!$medicaments) {
    $medicaments = []; 
}
           
    
$ruptureQuery = "SELECT numMedoc, Design, prix_unitaire, stock 
FROM MEDICAMENT 
WHERE stock < 5 
ORDER BY stock ASC";
$ruptureStmt = $pdo->query($ruptureQuery);
$medicamentsRupture = $ruptureStmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT montant_total FROM stats_recette_totale");
$recette_totale = $stmt->fetchColumn();
   
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - PharmaSys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        

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
        
        .recette-card {
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 10px;
            transition: transform 0.3s;
            border: none;
        }
        
        .recette-card:hover {
            transform: translateY(-3px);
        }
        
        
        .rounded-table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        
        .table-rupture thead {
            background: #3498db;
            color: white;
        }
        
        .table-rupture th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem;
        }
        
        .table-rupture td {
            vertical-align: middle;
            padding: 0.75rem 1rem;
        }
        
        .table-rupture tbody tr:nth-child(even) {
            background-color: rgba(0,0,0,0.02);
        }
        
        .table-rupture tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .stock-danger {
            font-weight: 700;
            color: #e74c3c;
            background-color: rgba(231, 76, 60, 0.1);
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
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
                <a href="dashboard.php" class="sidebar-link active">
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
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-pills me-2"></i>Gestion des Médicaments
                </h2>
            </div>

            <div class="recette-card bg-white p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-muted mb-1">
                            <i class="fas fa-wallet me-2"></i>Recette Totale
                        </h5>
                        <p class="mb-0 text-muted">Montant cumulé des ventes</p>
                    </div>
                    <span class="badge bg-success fs-5 px-3 py-2">
                    <?= number_format($recette_totale, 0, '', ' ') ?> Ar
                    </span>
                </div>
            </div>
            
            
            <div class="d-flex justify-content-between mb-4">
                <button class="btn btn-success btn-action" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i>Ajouter Médicament
                </button>
            </div>
            
            <!--  recherche -->
            <div class="row mb-4">
    <div class="col-md-6">
        <div class="search-container position-relative">
            <input type="text" id="searchInput" class="form-control search-input ps-5" 
                   placeholder="Rechercher un médicament...">
            <div class="search-icon">
                <i class="fas fa-search"></i>
            </div>
        </div>
    </div>
</div>


<style>
    .search-container {
        display: flex;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-radius: 50px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .search-container:focus-within {
        box-shadow: 0 2px 15px rgba(0, 123, 255, 0.25);
    }

    .search-input {
        border: none;
        padding: 12px 20px;
        flex-grow: 1;
        border-radius: 50px 0 0 50px !important;
    }

    .search-input:focus {
        outline: none;
        box-shadow: none;
    }

    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    .search-btn {
        border-radius: 0 50px 50px 0;
        padding: 12px 20px;
        border: none;
        white-space: nowrap;
        transition: all 0.3s;
    }

    .search-btn:hover {
        background-color: #0b5ed7;
    }

    /* Version responsive */
    @media (max-width: 768px) {
        .search-container {
            flex-direction: column;
            border-radius: 10px;
        }
        
        .search-input {
            border-radius: 10px 10px 0 0 !important;
            padding-left: 40px;
        }
        
        .search-btn {
            border-radius: 0 0 10px 10px !important;
            width: 100%;
        }
        
        .search-icon {
            left: 12px;
        }
    }
</style>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-rupture mb-0">
                            <thead class="fw-bold">
                                <tr>
                                    <th class="text-center ">Code</th>
                                    <th>Désignation</th>
                                    <th class="text-end">Prix Unitaire</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($medicaments as $medicament): ?>
                                <tr>
                                    <td class="text-center fw-bold"><?= htmlspecialchars($medicament['numMedoc']) ?></td>
                                    <td><?= htmlspecialchars($medicament['Design']) ?></td>
                                    <td class="text-end"><?= number_format($medicament['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                                    <td class="text-center">
                                        
                                            <?= htmlspecialchars($medicament['stock']) ?>
                                        
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning edit-btn"
                                            data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-nummedoc="<?= $medicament['numMedoc'] ?>"
                                            data-design="<?= $medicament['Design'] ?>"
                                            data-prix="<?= $medicament['prix_unitaire'] ?>"
                                            data-stock="<?= $medicament['stock'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-btn"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-nummedoc="<?= $medicament['numMedoc'] ?>">
                                            <i class="fas fa-trash"></i>
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
    </div>

    
    
    
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Ajouter Médicament
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../MEDICAMENT/ajouter.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add-numMedoc" class="form-label">code</label>
                            <input type="text" class="form-control" id="add-numMedoc" name="numMedoc" required>
                        </div>
                        <div class="mb-3">
                            <label for="add-Design" class="form-label">Désignation</label>
                            <input type="text" class="form-control" id="add-Design" name="Design" required>
                        </div>
                        <div class="mb-3">
                            <label for="add-prix" class="form-label">Prix Unitaire (Ar)</label>
                            <input type="number" class="form-control" id="add-prix" name="prix_unitaire" required min="0">
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
   
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Modifier Médicament
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../MEDICAMENT/modifier.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit-numMedoc" name="numMedoc">
                        <div class="mb-3">
                            <label for="edit-Design" class="form-label">Désignation</label>
                            <input type="text" class="form-control" id="edit-Design" name="Design" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-prix" class="form-label">Prix Unitaire (Ar)</label>
                            <input type="number" class="form-control" id="edit-prix" name="prix_unitaire" required min="0">
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div> 
    
    
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../MEDICAMENT/supprimer.php" method="POST">
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer ce médicament ? Cette action est irréversible.</p>
                        <input type="hidden" id="delete-numMedoc" name="numMedoc">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // slide ouverte
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('content').classList.toggle('sidebar-active');
        });
        
        // slide fermet
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    document.getElementById('sidebar').classList.remove('active');
                    document.getElementById('content').classList.remove('sidebar-active');
                }
            });
        });
        
        // modifier
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit-numMedoc').value = this.getAttribute('data-nummedoc');
                document.getElementById('edit-Design').value = this.getAttribute('data-design');
                document.getElementById('edit-prix').value = this.getAttribute('data-prix');
                document.getElementById('edit-stock').value = this.getAttribute('data-stock');
            });
        });
        
        // supprimer
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('delete-numMedoc').value = this.getAttribute('data-nummedoc');
            });
        });
        
        // recherche
        document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    const tableBody = document.querySelector('tbody');
    let foundResults = false;
    const existingMessage = document.getElementById('noResultsMessage');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    rows.forEach(row => {
        const designation = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        if (designation.includes(searchTerm)) {
            row.style.display = '';
            foundResults = true;
        } else {
            row.style.display = 'none';
        }
    });

    if (!foundResults && searchTerm.length > 0) {
        const noResultsRow = document.createElement('tr');
        noResultsRow.id = 'noResultsMessage';
        noResultsRow.innerHTML = `
            <td colspan="5" class="text-center py-4 text-muted">
                <i class="fas fa-search-minus fa-2x mb-3"></i>
                <h5>Aucun médicament trouvé</h5>
                <p class="mb-0">Essayez avec un autre terme de recherche</p>
            </td>
        `;
        tableBody.appendChild(noResultsRow);
    }
});
   document.getElementById('searchInput').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        console.log("Recherche en cours :", searchTerm);
    });
    </script>
<div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 1100; width: 350px;">
    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
            <div>
                <strong>Erreur</strong>
                <div class="mt-1"><?= htmlspecialchars($_GET['error']) ?></div>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle fa-lg me-3"></i>
            <div>
                <strong>Succès</strong>
                <div class="mt-1"><?= htmlspecialchars($_GET['success']) ?></div>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 4000);
    });
});
</script>
</body>
</html>