(function() {
    // Wait until DOM is fully loaded before initializing
    function init() {
        console.log("SketricGen Widget initializing in WordPress environment");
        
        let agentId = null;
        let apiKey = null;
        let styleConfig = {};
        
        // First try to get config from the global object
        if (window.SketricGenConfig) {
            console.log("Found SketricGen configuration object");
            agentId = window.SketricGenConfig.agentId;
            apiKey = window.SketricGenConfig.apiKey;
            styleConfig = window.SketricGenConfig.styleConfig || {};
        } else {
            // Fallback to the original method using script attributes
            console.log("Looking for SketricGen configuration in script attributes");
            const scriptElements = document.getElementsByTagName('script');
            
            for (let i = 0; i < scriptElements.length; i++) {
                const script = scriptElements[i];
                if (script.src && script.src.includes('sketricgen-widget.js')) {
                    agentId = script.getAttribute('data-agent-id');
                    apiKey = script.getAttribute('data-api-key');
                    
                    // Parse the style configuration JSON
                    const styleConfigStr = script.getAttribute('data-style-config');
                    if (styleConfigStr) {
                        try {
                            styleConfig = JSON.parse(styleConfigStr);
                        } catch (e) {
                            console.error("SketricGen widget: Invalid style configuration JSON", e);
                        }
                    }
                    break;
                }
            }
        }
        
        // Debug output
        console.log("SketricGen configuration - Agent ID found:", !!agentId, "API Key found:", !!apiKey);
        
        if (!agentId || !apiKey) {
            console.error("SketricGen widget: Missing required configuration (Agent ID or API Key)");
            return;
        }
        
        // Initialize the widget with the found configuration
        if (typeof window.SketricWidget === 'function') {
            // Pass configuration to the widget
            window.sketricWidgetInstance = new window.SketricWidget(agentId, {
                apiKey: apiKey,
                ...styleConfig
            });
        } else {
            // Load the main widget script dynamically if not already available
            const mainScript = document.createElement('script');
            mainScript.src = 'https://skgen-widget.s3.us-east-1.amazonaws.com/sketricgen-widget.js';
            mainScript.onload = function() {
                if (typeof window.SketricWidget === 'function') {
                    window.sketricWidgetInstance = new window.SketricWidget(agentId, {
                        apiKey: apiKey,
                        ...styleConfig
                    });
                } else {
                    console.error("SketricGen Widget constructor not found after loading script");
                }
            };
            document.head.appendChild(mainScript);
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
