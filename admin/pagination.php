<?php
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) return '';
    
    $output = '<div class="pagination-container"><div class="pagination">';
    
    // Previous page arrow
    if ($currentPage > 1) {
        $output .= '<a href="' . $baseUrl . '&page=' . ($currentPage - 1) . '" class="pagination-arrow prev">&lt;</a>';
    } else {
        $output .= '<span class="pagination-arrow prev disabled">&lt;</span>';
    }
    
    // First page
    $output .= '<a href="' . $baseUrl . '&page=1" class="' . ($currentPage == 1 ? 'active' : '') . '">1</a>';
    
    // Determine page range to display
    $startPage = max(2, $currentPage - 1);
    $endPage = min($totalPages - 1, $currentPage + 1);
    
    // Add ellipsis after first page if needed
    if ($startPage > 2) {
        $output .= '<span class="ellipsis page-jump" data-max="' . ($startPage - 1) . '">...</span>';
    }
    
    // Page numbers
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == 1 || $i == $totalPages) continue; // Skip first and last page as they're handled separately
        $output .= '<a href="' . $baseUrl . '&page=' . $i . '" class="' . ($currentPage == $i ? 'active' : '') . '">' . $i . '</a>';
    }
    
    // Add ellipsis before last page if needed
    if ($endPage < $totalPages - 1) {
        $output .= '<span class="ellipsis page-jump" data-max="' . ($totalPages - 1) . '">...</span>';
    }
    
    // Last page (if more than one page)
    if ($totalPages > 1) {
        $output .= '<a href="' . $baseUrl . '&page=' . $totalPages . '" class="' . ($currentPage == $totalPages ? 'active' : '') . '">' . $totalPages . '</a>';
    }
    
    // Next page arrow
    if ($currentPage < $totalPages) {
        $output .= '<a href="' . $baseUrl . '&page=' . ($currentPage + 1) . '" class="pagination-arrow next">&gt;</a>';
    } else {
        $output .= '<span class="pagination-arrow next disabled">&gt;</span>';
    }
    
    $output .= '</div></div>';
    
    // Add JavaScript for page jump functionality
    $output .= '
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const ellipses = document.querySelectorAll(".page-jump");
        ellipses.forEach(ellipsis => {
            ellipsis.addEventListener("click", function() {
                const maxPage = parseInt(this.getAttribute("data-max"));
                const currentUrl = window.location.href;
                const baseUrl = currentUrl.split("&page=")[0].split("?page=")[0];
                
                // Create a clone of the ellipsis to restore it later
                const ellipsisClone = this.cloneNode(true);
                
                // Replace ellipsis with input field
                const input = document.createElement("input");
                input.type = "number";
                input.min = "1";
                input.max = maxPage;
                input.className = "page-input";
                input.style.width = "40px";
                input.style.textAlign = "center";
                input.style.padding = "2px";
                input.style.margin = "0 4px";
                
                // More thorough removal of up/down arrows from number input
                input.style.appearance = "textfield";
                input.style.MozAppearance = "textfield";
                input.style.webkitAppearance = "none";
                input.style.margin = "0";
                
                // Add CSS to remove spinner buttons
                var styleSheet = document.createElement("style");
                styleSheet.innerText = ".page-input::-webkit-outer-spin-button, .page-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }";
                document.head.appendChild(styleSheet);
                
                // Replace the ellipsis with the input
                this.parentNode.replaceChild(input, this);
                input.focus();
                
                // Add click event listener to the clone for future use
                ellipsisClone.addEventListener("click", arguments.callee);
                
                // Handle input events
                input.addEventListener("keydown", function(e) {
                    if (e.key === "Enter") {
                        const pageNum = parseInt(this.value);
                        if (pageNum >= 1 && pageNum <= ' . $totalPages . ') {
                            window.location.href = baseUrl + (baseUrl.includes("?") ? "&" : "?") + "page=" + pageNum;
                        }
                    }
                    
                    if (e.key === "Escape") {
                        // Restore the ellipsis if user presses escape
                        this.parentNode.replaceChild(ellipsisClone, this);
                    }
                });
                
                // Handle blur event (clicking outside)
                input.addEventListener("blur", function() {
                    this.parentNode.replaceChild(ellipsisClone, this);
                });
            });
        });
    });
    </script>';
    
    return $output;
}
?>