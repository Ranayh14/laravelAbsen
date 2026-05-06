/**
 * SVG Robot Cat Character Display
 * Shows different emotions with SVG animations
 */

(function() {
    function init() {
        console.log('[RobotCat] Initializing...');
        const characterContainer = document.getElementById('robot-cat-character');
        if (characterContainer) {
            loadRobotCatCharacter();
            
            // Refresh every 5 minutes
            setInterval(loadRobotCatCharacter, 5 * 60 * 1000);
        } else {
            console.warn('[RobotCat] Container #robot-cat-character not found');
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

/**
 * Load and display robot cat with appropriate emotion
 */
async function loadRobotCatCharacter() {
    console.log('[RobotCat] Loading character states...');
    
    try {
        let missingReports = 0;
        let apiMethod = 'none';
        
        // Try to use the standard dashboard API if available
        if (typeof api === 'function') {
            apiMethod = 'api-standard';
            console.log('[RobotCat] Using standard api() function');
            const result = await api('?ajax=get_missing_daily_reports', { _t: Date.now() }, { suppressModal: true, cache: false });
            console.log('[RobotCat] API Result:', result);
            
            if (result && result.ok && Array.isArray(result.data)) {
                missingReports = result.data.length;
            } else if (result && Array.isArray(result)) {
                // Fallback for different return format
                missingReports = result.length;
            }
        } else {
            apiMethod = 'fetch-fallback';
            console.log('[RobotCat] Using fetch-fallback');
            // Use current page URL as base for relative query
            const baseUrl = window.location.origin + window.location.pathname;
            const response = await fetch(baseUrl + '?ajax=get_missing_daily_reports&_t=' + Date.now());
            console.log('[RobotCat] Fetch Response Status:', response.status);
            
            if (response.ok) {
                const json = await response.json();
                console.log('[RobotCat] Fetch JSON:', json);
                if (json && json.ok && Array.isArray(json.data)) {
                    missingReports = json.data.length;
                } else if (Array.isArray(json)) {
                    missingReports = json.length;
                }
            }
        }
        
        console.log('[RobotCat] Statistics:', { missingReports, apiMethod });
        
        // Determine emotion based on missing reports
        // 0: Happy, 1-5: Sad, 6+: Angry
        let emotion = 'happy';
        if (missingReports >= 6) {
            emotion = 'angry';
        } else if (missingReports >= 1) {
            emotion = 'sad';
        }
        
        console.log('[RobotCat] Final Emotion determined:', emotion);
        
        // Update SVG states
        updateRobotCatEmotion(emotion);
        
    } catch (error) {
        console.error('[RobotCat] Critical Error in loadRobotCatCharacter:', error);
        // Default to happy on error so it doesn't stay frozen/broken
        updateRobotCatEmotion('happy');
    }
}

/**
 * Update robot cat emotion by toggling SVG states
 */
function updateRobotCatEmotion(mode) {
    const container = document.getElementById('robot-cat-character');
    if (!container) return;
    
    const states = ['happy', 'sad', 'angry'];
    
    // Remove all emotion classes
    container.classList.remove('emotion-happy', 'emotion-sad', 'emotion-angry');
    
    // Add current emotion class
    container.classList.add('emotion-' + mode);
    
    // Toggle SVG state visibility
    states.forEach(s => {
        const headState = document.getElementById(`head-${s}-state`);
        const tailState = document.getElementById(`tail-${s}-state`);
        
        if (headState && tailState) {
            if (s === mode) {
                headState.classList.remove('hidden');
                tailState.classList.remove('hidden');
            } else {
                headState.classList.add('hidden');
                tailState.classList.add('hidden');
            }
        }
    });

    // Update body light color
    const bodyLight = document.getElementById('body-light');
    if (bodyLight) {
        if (mode === 'angry') {
            bodyLight.setAttribute('fill', 'var(--glow-red)');
            bodyLight.classList.remove('glow-cyan');
            bodyLight.classList.add('glow-red');
        } else {
            bodyLight.setAttribute('fill', 'var(--glow-cyan)');
            bodyLight.classList.remove('glow-red');
            bodyLight.classList.add('glow-cyan');
        }
    }
    
    console.log('[RobotCat] Updated to emotion:', mode);
}
