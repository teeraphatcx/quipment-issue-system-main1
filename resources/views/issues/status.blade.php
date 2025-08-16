<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <title>ตรวจสอบสถานะ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        .text-orange { color: #e36200; }
        img.issue-img { width: 60px; height: auto; border-radius: 4px; }
    </style>
</head>
<body>
<header class="bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
    <h5 class="mb-1 fw-bold">
        <a href="/" class="text-orange text-decoration-none">
            ระบบแจ้งปัญหาการใช้งานอุปกรณ์ห้องเรียน ห้องปฏิบัติการ ห้องประชุม
        </a>
    </h5>
    <nav>
        <ul class="nav align-items-center gap-3 mb-0">
            <li class="nav-item">
                <a class="nav-link text-secondary" href="/issues/status">ตรวจสอบสถานะ</a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center text-secondary" href="/faq">
                    <i class="bi bi-question-circle me-1"></i>คำถามที่พบบ่อย
                </a>
            </li>
            <li><div class="vr"></div></li>
            <li class="nav-item">
                <a href="/admin/login" class="btn btn-warning ms-3">สำหรับเจ้าหน้าที่</a>
            </li>
        </ul>
    </nav>
</header>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary fw-bold"><i class="bi bi-tools"></i> ตรวจสอบสถานะการแจ้งปัญหา</h2>
        <button class="btn btn-success" onclick="refreshData()" id="refreshBtn">
            <i class="bi bi-arrow-clockwise me-2"></i>รีเฟรช
        </button>
    </div>

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-bordered align-middle table-hover">
            <thead class="table-light text-center">
                <tr>
                    <th>ห้อง / อุปกรณ์</th>
                    <th>เรื่องที่แจ้ง</th>
                    <th>รูปภาพ</th>
                    <th>สถานะ</th>
                    <th>วันที่แจ้ง</th>
                    <th>รายละเอียด</th>
                </tr>
            </thead>
            <tbody id="issuesTableBody">
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-hourglass-split"></i> กำลังโหลดข้อมูล...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="text-end mt-3 text-muted">
        <small>อัพเดตล่าสุด: <span id="lastUpdated">-</span></small>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('th-TH', {
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit'
    });
}

function getStatusBadge(status) {
    switch(status) {
        case 'pending': return '<span class="badge bg-warning text-dark">รอดำเนินการ</span>';
        case 'replied': return '<span class="badge bg-success">ตอบแล้ว</span>';
        case 'in_progress': return '<span class="badge bg-info text-dark">กำลังดำเนินการ</span>';
        case 'done': return '<span class="badge bg-success">เสร็จสิ้น</span>';
        default: return '<span class="badge bg-secondary">ไม่ทราบสถานะ</span>';
    }
}

function renderIssues(issues) {
    const tbody = document.getElementById('issuesTableBody');
    if (!issues || issues.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">
            <i class="bi bi-inbox"></i> ไม่มีรายการแจ้งปัญหาในขณะนี้
        </td></tr>`;
        return;
    }

    tbody.innerHTML = issues.map(issue => `
        <tr>
            <td class="text-center">${(issue.room || '-') + ' / ' + (issue.equipment || '-')}</td>
            <td>${issue.description || '-'}</td>
            <td class="text-center">
                ${issue.image_path 
                    ? `<a href="/storage/${issue.image_path}" target="_blank">
                        <img src="/storage/${issue.image_path}" class="issue-img" alt="รูปภาพ">
                       </a>` 
                    : '-'}
            </td>
            <td class="text-center">${getStatusBadge(issue.status)}</td>
            <td class="text-center">${formatDateTime(issue.created_at)}</td>
            <td class="text-center">
                <!-- ปุ่มลูกตา ดูรายละเอียด + Modal -->
                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailModal${issue.id}" title="ดูรายละเอียด">
                    <i class="bi bi-eye"></i>
                </button>
                <div class="modal fade" id="detailModal${issue.id}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">รายละเอียดการแจ้งปัญหา #${issue.id}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>ห้อง / อุปกรณ์:</strong> ${(issue.room || '-') + ' / ' + (issue.equipment || '-')}</p>
                                <p><strong>เรื่องที่แจ้ง:</strong> ${issue.description || '-'}</p>
                                ${issue.reply ? `<div class="mt-2 p-2 bg-light rounded border"><strong>ตอบกลับจากแอดมิน:</strong> ${issue.reply}</div>` : ''}
                                ${issue.image_path 
                                    ? `<p><strong>รูปภาพ:</strong><br><img src="/storage/${issue.image_path}" class="img-fluid rounded" alt="รูปภาพ"></p>` 
                                    : ''}
                                <p><strong>สถานะ:</strong> ${getStatusBadge(issue.status)}</p>
                                <p><strong>วันที่แจ้ง:</strong> ${formatDateTime(issue.created_at)}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    `).join('');
}

async function refreshData() {
    const refreshBtn = document.getElementById('refreshBtn');
    const originalContent = refreshBtn.innerHTML;
    try {
        refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังโหลด...';
        refreshBtn.disabled = true;

        const response = await fetch('/issues/status', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();
        renderIssues(data.issues || data);
        document.getElementById('lastUpdated').textContent = new Date().toLocaleString('th-TH');
    } catch (error) {
        console.error('Error fetching data:', error);
        document.getElementById('issuesTableBody').innerHTML = `
            <tr><td colspan="6" class="text-center text-danger py-4">
                <i class="bi bi-exclamation-triangle"></i> ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง
            </td></tr>`;
        document.getElementById('lastUpdated').textContent = 'เกิดข้อผิดพลาด';
    } finally {
        refreshBtn.innerHTML = originalContent;
        refreshBtn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    refreshData();
    setInterval(refreshData, 30000);
});
document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshData(); });
</script>
</body>
</html>
