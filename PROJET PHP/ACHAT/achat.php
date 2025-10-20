<?php
include("../MEDICAMENT/db.php");

$query = "SELECT A.numAchat, A.nomClient, A.dateAchat, 
          COALESCE(M.Design, DA.design_archive) AS Design,
          DA.numMedoc, DA.nbr, DA.design_archive
          FROM ACHAT A
          LEFT JOIN DETAIL_ACHAT DA ON A.numAchat = DA.numAchat
          LEFT JOIN MEDICAMENT M ON DA.numMedoc = M.numMedoc
          ORDER BY A.numAchat, Design";

$result = $pdo->query($query);
$achats = $result->fetchAll(PDO::FETCH_ASSOC);
$sqlMeds = "SELECT numMedoc, Design FROM MEDICAMENT WHERE stock > 0 ORDER BY Design";
$stmtMeds = $pdo->query($sqlMeds);
$medicaments = $stmtMeds->fetchAll(PDO::FETCH_ASSOC);
$ruptureQuery = "SELECT numMedoc, Design, prix_unitaire, stock 
                 FROM MEDICAMENT 
                 WHERE stock < 5 
                 ORDER BY stock ASC";
$ruptureStmt = $pdo->query($ruptureQuery);
$medicamentsRupture = $ruptureStmt->fetchAll(PDO::FETCH_ASSOC);
$grouped = [];
foreach ($achats as $achat) {
    $grouped[$achat['numAchat']]['info'] = [
        'nomClient' => $achat['nomClient'],
        'dateAchat' => $achat['dateAchat']
    ];
    $grouped[$achat['numAchat']]['meds'][] = [
        'Design' => $achat['Design'],
        'nbr' => $achat['nbr'],
        'numMedoc' => $achat['numMedoc']
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Achats - PharmaSys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --header-bg: #ffffff;
            --header-text: #000000;
            --danger-bg: #dc3545;
            --danger-text: white;
            --success-bg: #d4edda;
            --success-text: #155724;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        
        .page-header h2 {
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .table-achats {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        
        .table-achats thead {
            background: #3498db;
            color: white;
        }
        
        .table-achats th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            vertical-align: middle;
            text-align: center;
        }
        
        .table-achats td {
            vertical-align: middle;
            padding: 0.75rem 1rem;
        }
        
        .table-achats tbody tr:nth-child(even) {
            background-color: rgba(0,0,0,0.02);
        }
        
        .table-achats tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .btn-action {
            border-radius: 50px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s;
            margin: 2px;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
        
        .medicament-group {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            position: relative;
            background-color: #f9f9f9;
        }
        
        .remove-med {
            color: #dc3545;
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 1.2rem;
        }
        
        .badge-rupture {
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 50px;
            background-color: white;
            color: #e74c3c;
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
            <a href="../ENTREE/interface.php" class="sidebar-link">
                <i class="fas fa-pills"></i> Entrée Médicaments
            </a>
        </li>
        <li>
            <a href="achat.php" class="sidebar-link active">
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

        <div class="page-header">
            <h2 class="text-center mb-0">
                <i class="fas fa-shopping-cart me-2"></i>Gestion des Achats
            </h2>
        </div>
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-4">
                    <button type="button" class="btn btn-success btn-action" data-bs-toggle="modal" data-bs-target="#modalAjout">
                        <i class="fas fa-plus-circle me-2"></i> Nouvel Achat
                    </button>
                </div>
                
                <?php if (isset($_GET['success'])) : ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($_GET['success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])) : ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($_GET['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-achats">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Client</th>
                                <th>Médicament</th>
                                <th>Quantité</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($achats)) : ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-shopping-cart fa-2x mb-3"></i>
                                        <h5>Aucun achat enregistré</h5>
                                        <p>Commencez par ajouter un nouvel achat</p>
                                    </td>
                                </tr>
                            <?php else : ?>
                                <?php 
                                $currentAchat = null;
                                foreach ($achats as $achat) : 
                                    if ($currentAchat !== $achat['numAchat']) :
                                        $currentAchat = $achat['numAchat'];
                                        $rowspan = count(array_filter($achats, function($a) use ($currentAchat) {
                                            return $a['numAchat'] === $currentAchat;
                                        }));
                                ?>
                                    <tr>
                                        <td rowspan="<?= $rowspan ?>" class="text-center fw-bold">
                                            <?= htmlspecialchars($achat['numAchat']) ?>
                                        </td>
                                        <td rowspan="<?= $rowspan ?>">
                                            <?= htmlspecialchars($achat['nomClient']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($achat['Design']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($achat['nbr']) ?></td>
                                        <td rowspan="<?= $rowspan ?>" class="text-center">
                                            <?= htmlspecialchars($achat['dateAchat']) ?>
                                        </td>
                                        <td rowspan="<?= $rowspan ?>" class="text-center">
                                            <button class="btn btn-warning btn-action" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal<?= $achat['numAchat'] ?>"
                                                title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-action" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal<?= $achat['numAchat'] ?>"
                                                title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <a href="facture.php?numAchat=<?= $achat['numAchat'] ?>" 
                                               class="btn btn-info btn-action" 
                                               target="_blank"
                                               title="Générer la facture PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php else : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($achat['Design']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($achat['nbr']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAjout" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Ajouter un Achat
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="../ACHAT/ajouter.php" method="post" id="formAchat">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Numéro d'Achat</label>
                            <input type="text" name="numAchat" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Client</label>
                            <input type="text" name="nomClient" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="dateAchat" class="form-control" max="<?= date('Y-m-d') ?>" required>
                    </div>

                    <h5 class="mb-3">
                        <i class="fas fa-pills me-2"></i>Médicaments
                        <span class="badge bg-primary rounded-pill ms-2" id="medCount"></span>
                    </h5>
                    
                    <div id="medicaments-container">
                        <div class="medicament-group">
                            <span class="remove-med" onclick="removeMedicament(this)">✖</span>
                            <div class="row">
                                <div class="col-md-8">
                                    <label class="form-label">Médicament</label>
                                    <select name="numMedoc[]" class="form-select" required>
                                        <option value="">Sélectionnez un médicament</option>
                                        <?php foreach ($medicaments as $med) : ?>
                                            <option value="<?= htmlspecialchars($med['numMedoc']) ?>">
                                                <?= htmlspecialchars($med['Design']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Quantité</label>
                                    <input type="number" name="nbr[]" class="form-control" min="1" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-primary" id="addMedicament">
                            <i class="fas fa-plus me-2"></i>Ajouter un médicament
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-2"></i>Enregistrer l'achat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php foreach ($grouped as $numAchat => $data) : ?>
    Copy

<div class="modal fade" id="editModal<?= $numAchat ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Modifier Achat <?= $numAchat ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../ACHAT/modifier.php" method="post">
                <input type="hidden" name="numAchat" value="<?= $numAchat ?>">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Client</label>
                            <input type="text" name="nomClient" class="form-control" 
                                   value="<?= htmlspecialchars($data['info']['nomClient']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" name="dateAchat" class="form-control" 
                                   value="<?= htmlspecialchars($data['info']['dateAchat']) ?>" required>
                        </div>
                    </div>
                    
                    <h5 class="mb-3">
                        <i class="fas fa-pills me-2"></i>Médicaments existants
                        <span class="badge bg-warning text-dark rounded-pill ms-2"><?= count($data['meds']) ?></span>
                    </h5>
                    
                    <?php foreach ($data['meds'] as $index => $med) : 
                        $design = $med['Design'] ?? $med['design_archive'];
                    ?>
                    <div class="medicament-group mb-3 position-relative border p-3 rounded">
                        <div class="form-check check-suppression" style="display:none;">
                            <input class="form-check-input" type="checkbox" 
                                   name="supprimer_meds[]" value="<?= $med['numMedoc'] ?>" id="suppr<?= $numAchat ?>_<?= $med['numMedoc'] ?>">
                            <label class="form-check-label text-danger" for="suppr<?= $numAchat ?>_<?= $med['numMedoc'] ?>">
                                <i class="fas fa-trash-alt me-1"></i>Supprimer
                            </label>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <label class="form-label">Médicament</label>
                                <input type="text" class="form-control" 
                                       value="<?= htmlspecialchars($design) ?>" readonly>
                                <input type="hidden" name="numMedoc_existants[]" value="<?= $med['numMedoc'] ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Quantité</label>
                                <input type="number" 
                                       name="medicaments_existants[<?= $med['numMedoc'] ?>]" 
                                       class="form-control" 
                                       value="<?= $med['nbr'] ?>" 
                                       min="1" 
                                       required>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <h5 class="mb-3 mt-4">
                        <i class="fas fa-plus-circle me-2"></i>Ajouter des médicaments
                    </h5>
                    <div id="nouveauxMedicaments<?= $numAchat ?>"></div>
                    
                    <button type="button" class="btn btn-sm btn-primary mt-2" 
                            onclick="ajouterMedicament('<?= $numAchat ?>')">
                        <i class="fas fa-plus me-1"></i>Ajouter un médicament
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger me-auto" id="btnToggleSuppression<?= $numAchat ?>">
                        <i class="fas fa-minus-circle me-2"></i>Mode Suppression
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <div class="modal fade" id="deleteModal<?= $numAchat ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-trash me-2"></i>Supprimer Achat <?= $numAchat ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Confirmez-vous la suppression de cet achat ?</p>
                    <div class="mb-3">
                        <strong>Client:</strong> <?= htmlspecialchars($data['info']['nomClient']) ?>
                    </div>
                    <div class="mb-3">
                        <strong>Date:</strong> <?= htmlspecialchars($data['info']['dateAchat']) ?>
                    </div>
                    <div class="mb-3">
                        <strong>Médicaments:</strong>
                        <ul class="list-group">
                            <?php foreach ($data['meds'] as $med) : ?>
                                <li class="list-group-item">
                                    <?= htmlspecialchars($med['Design']) ?> (x<?= $med['nbr'] ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <form action="../ACHAT/supprimer.php" method="post">
                        <input type="hidden" name="numAchat" value="<?= $numAchat ?>">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Annuler
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-check me-2"></i>Confirmer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

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

    const medContainer = document.getElementById('medicaments-container');
    const medCount = document.getElementById('medCount');
    
    document.getElementById('addMedicament').addEventListener('click', function() {
        const newItem = medContainer.firstElementChild.cloneNode(true);
        newItem.querySelector('select').selectedIndex = 0;
        newItem.querySelector('input[type="number"]').value = '';
        medContainer.appendChild(newItem);
        updateMedCount();
    });
    function removeMedicament(element) {
        if (medContainer.children.length > 1) {
            element.parentElement.remove();
            updateMedCount();
        } else {
            alert('Vous devez avoir au moins un médicament.');
        }
    }

    function updateMedCount() {
        medCount.textContent = medContainer.children.length;
    }

    updateMedCount();

    function ajouterMedicament(numAchat) {
        const container = document.getElementById(`nouveauxMedicaments${numAchat}`);
        const counter = container.querySelectorAll('.medicament-group').length + 1;
        
        container.insertAdjacentHTML('beforeend', `
            <div class="medicament-group mb-3 border p-3 rounded">
                <button type="button" class="btn btn-sm btn-danger float-end" 
                        onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Médicament</label>
                        <select name="nouveaux_medicaments[${counter}][numMedoc]" 
                                class="form-select" required>
                            <option value="">Choisir un médicament</option>
                            <?php foreach ($medicaments as $med) : ?>
                                <option value="<?= $med['numMedoc'] ?>">
                                    <?= htmlspecialchars($med['Design']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Quantité</label>
                        <input type="number" 
                               name="nouveaux_medicaments[${counter}][quantite]" 
                               class="form-control" 
                               min="1" 
                               value="1" 
                               required>
                    </div>
                </div>
            </div>
        `);
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[id^="btnToggleSuppression"]').forEach(btn => {
            btn.addEventListener('click', function() {
                const modalId = this.closest('.modal').id;
                const checkboxes = document.querySelectorAll(`#${modalId} .check-suppression`);
                
                checkboxes.forEach(checkbox => {
                    checkbox.style.display = checkbox.style.display === 'none' ? 'block' : 'none';
                });
                
                this.classList.toggle('btn-danger');
                this.classList.toggle('btn-outline-danger');
                
                const icon = this.querySelector('i');
                if (this.classList.contains('btn-danger')) {
                    icon.classList.replace('fa-minus-circle', 'fa-times');
                    this.innerHTML = '<i class="fas fa-times me-2"></i>Annuler Suppression';
                } else {
                    icon.classList.replace('fa-times', 'fa-minus-circle');
                    this.innerHTML = '<i class="fas fa-minus-circle me-2"></i>Mode Suppression';
                }
            });
        });
    });
</script>
</body>
</html>