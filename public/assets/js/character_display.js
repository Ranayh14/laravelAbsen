/**
 * Character Display Script
 * Handles loading and displaying employee 3D character based on report completion
 */

// Load employee character when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Only load character if we're on employee dashboard
    const characterElement = document.getElementById('employee-character');
    if (characterElement) {
        loadEmployeeCharacter();
        
        // Refresh character every 5 minutes to reflect updated report status
        setInterval(loadEmployeeCharacter, 5 * 60 * 1000);
    }
});

/**
 * Load and display appropriate character based on missing reports count
 */
async function loadEmployeeCharacter() {
    console.log('[Character] Starting to load employee character...');
    
    try {
        const response = await fetch('config.php?action=get_employee_character', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        });
        
        console.log('[Character] Response status:', response.status);
        console.log('[Character] Response ok:', response.ok);
        
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        
        const text = await response.text();
        console.log('[Character] Response text length:', text.length);
        console.log('[Character] Response preview:', text.substring(0, 200));
        
        const data = JSON.parse(text);
        console.log('[Character] Parsed data:', {
            success: data.success,
            emotion: data.emotion,
            missing_reports: data.missing_reports,
            has_character: !!data.character,
            character_length: data.character ? data.character.length : 0
        });
        
        if (data.success && data.character) {
            const characterImg = document.getElementById('employee-character');
            if (characterImg) {
                console.log('[Character] Setting image source...');
                characterImg.src = data.character;
                characterImg.style.display = 'block';
                
                // Hide fallback emoji
                const fallbackSpan = characterImg.nextElementSibling;
                if (fallbackSpan) {
                    fallbackSpan.style.display = 'none';
                }
                
                // Add emotion class for potential animations
                characterImg.className = 'w-full h-full object-cover character-' + (data.emotion || 'happy');
                
                console.log('[Character] Character loaded successfully! Emotion:', data.emotion);
            } else {
                console.error('[Character] Element #employee-character not found!');
            }
        } else if (data.error) {
            console.error('[Character] API error:', data.error);
            showFallbackCharacter();
        } else {
            console.error('[Character] No character data in response');
            showFallbackCharacter();
        }
    } catch (error) {
        console.error('[Character] Error loading employee character:', error);
        showFallbackCharacter();
    }
}

/**
 * Show fallback character (robot emoji) if loading fails
 */
function showFallbackCharacter() {
    const characterImg = document.getElementById('employee-character');
    const fallbackSpan = characterImg ? characterImg.nextElementSibling : null;
    
    if (characterImg) {
        characterImg.style.display = 'none';
    }
    
    if (fallbackSpan) {
        fallbackSpan.style.display = 'flex';
    }
    
    console.log('Character loading failed, showing robot emoji fallback');
}

/**
 * Trigger character regeneration (for admin use)
 * @param {number} userId User ID to regenerate character for
 */
async function regenerateCharacter(userId) {
    try {
        const response = await fetch('config.php?action=regenerate_character', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'user_id=' + userId
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Karakter berhasil di-generate ulang!', 'success');
            // Reload character if on employee dashboard
            if (document.getElementById('employee-character')) {
                loadEmployeeCharacter();
            }
        } else {
            showToast('Gagal generate karakter: ' + (data.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Error regenerating character:', error);
        showToast('Terjadi kesalahan saat generate karakter', 'error');
    }
}
