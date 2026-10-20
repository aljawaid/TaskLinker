// Builds the interactive graph only when TaskLinker's graph page is present.
// Related test file: tests/PresentationCompatibilityTest.php.
(function () {
    'use strict';

    function readGraphData(elementId) {
        var element = document.getElementById(elementId);

        if (element === null) {
            return [];
        }

        try {
            return JSON.parse(element.textContent || '[]');
        } catch {
            return [];
        }
    }

    function initializeGraph() {
        var container = document.getElementById('task-linker-network');

        if (container === null || typeof window.vis === 'undefined') {
            return;
        }

        var nodes = new window.vis.DataSet(readGraphData('task-linker-nodes'));
        var edges = new window.vis.DataSet(readGraphData('task-linker-edges'));
        var network = new window.vis.Network(container, {nodes: nodes, edges: edges}, {
            autoResize: true,
            layout: {
                improvedLayout: true
            },
            interaction: {
                hover: true,
                keyboard: {
                    enabled: true,
                    bindToWindow: false
                },
                navigationButtons: true
            },
            nodes: {
                margin: 12,
                widthConstraint: {
                    maximum: 260
                }
            },
            edges: {
                color: {
                    color: '#64748b',
                    highlight: '#2563eb',
                    hover: '#2563eb'
                },
                font: {
                    align: 'top',
                    color: '#475569',
                    strokeWidth: 4,
                    strokeColor: '#ffffff'
                },
                smooth: {
                    enabled: true,
                    type: 'dynamic'
                }
            },
            physics: {
                barnesHut: {
                    avoidOverlap: 0.7,
                    springLength: 180
                },
                stabilization: {
                    iterations: 180,
                    updateInterval: 25
                }
            }
        });

        network.once('stabilizationIterationsDone', function () {
            network.fit({animation: false});
        });

        network.on('selectNode', function (parameters) {
            if (parameters.nodes.length !== 1) {
                return;
            }

            var node = nodes.get(parameters.nodes[0]);
            if (node && typeof node.url === 'string' && node.url !== '') {
                window.location.assign(node.url);
            }
        });

        var fitButton = document.querySelector('[data-task-linker-action="fit"]');
        if (fitButton !== null) {
            fitButton.addEventListener('click', function () {
                network.fit({animation: false});
                container.focus();
            });
        }

        if (typeof window.ResizeObserver === 'function') {
            var observer = new window.ResizeObserver(function () {
                network.redraw();
            });
            observer.observe(container);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeGraph);
    } else {
        initializeGraph();
    }
}());
