
// ============================================
// IA LOCAL (Funciona sin internet)
// ============================================

function analizarConIALocal(texto, titulo = '') {
    const textoCompleto = (titulo + ' ' + texto).toLowerCase();
    
    // Palabras clave para categorías
    const categorias = {
        'bache': ['bache', 'hoyo', 'cráter', 'bacheo', 'asfalto roto', 'calle rota', 'pavimento', 'hundimiento', 'grieta'],
        'fuga_agua': ['fuga', 'agua', 'tubería', 'inundación', 'chorro', 'escape', 'humedad', 'filtración', 'manguera', 'potable'],
        'basura': ['basura', 'residuos', 'desperdicios', 'tiradero', 'desechos', 'suciedad', 'acumulación', 'vertedero', 'contenedor', 'desecho'],
        'luminaria': ['luz', 'farol', 'lámpara', 'alumbrado', 'luminaria', 'foco', 'oscuridad', 'apagado', 'poste', 'alumbrado público'],
        'senal': ['señal', 'semáforo', 'tránsito', 'tráfico', 'letrero', 'indicador', 'señalamiento', 'alto', 'pare']
    };
    
    // Palabras clave para prioridad
    const prioridadAlta = ['urgente', 'peligro', 'accidente', 'grave', 'emergencia', 'inmediato', 'riesgo', 'peligroso', 'caída', 'lesión', 'herido', 'muerte', 'colapso'];
    const prioridadMedia = ['molesto', 'problema', 'daño', 'avería', 'mal estado', 'regular', 'afecta'];
    const prioridadBaja = ['pequeño', 'leve', 'menor', 'estético', 'cosmético', 'detalle', 'superficial'];
    
    // Detectar categoría
    let categoriaDetectada = 'otros';
    let maxPuntaje = 0;
    
    for (const [categoria, palabras] of Object.entries(categorias)) {
        let puntaje = 0;
        for (const palabra of palabras) {
            if (textoCompleto.includes(palabra)) {
                puntaje += 10;
            }
            // Buscar palabras similares
            const palabrasTexto = textoCompleto.split(' ');
            for (const pt of palabrasTexto) {
                if (pt.length > 3 && palabra.includes(pt.substring(0, 3))) {
                    puntaje += 2;
                }
            }
        }
        if (puntaje > maxPuntaje) {
            maxPuntaje = puntaje;
            categoriaDetectada = categoria;
        }
    }
    
    // Detectar prioridad
    let prioridadDetectada = 'media';
    let puntajeAlta = 0;
    let puntajeMedia = 0;
    let puntajeBaja = 0;
    
    for (const palabra of prioridadAlta) {
        if (textoCompleto.includes(palabra)) puntajeAlta += 10;
    }
    for (const palabra of prioridadMedia) {
        if (textoCompleto.includes(palabra)) puntajeMedia += 5;
    }
    for (const palabra of prioridadBaja) {
        if (textoCompleto.includes(palabra)) puntajeBaja += 5;
    }
    
    if (puntajeAlta > 5) prioridadDetectada = 'alta';
    else if (puntajeBaja > puntajeMedia && puntajeBaja > 3) prioridadDetectada = 'baja';
    
    // Calcular confianza
    let confianza = Math.min(95, Math.max(50, maxPuntaje + (puntajeAlta + puntajeMedia + puntajeBaja)));
    if (maxPuntaje < 10 && puntajeAlta + puntajeMedia + puntajeBaja < 5) {
        confianza = 30;
    }
    
    return {
        success: true,
        categoria: categoriaDetectada,
        prioridad: prioridadDetectada,
        confianza: confianza,
        metodo: 'local'
    };
}

// ============================================
// Gemini API (Si está disponible)
// ============================================

async function analizarConGemini(texto, titulo = '') {
    if (!GEMINI_API_KEY) {
        return null; // No hay API key
    }
    
    const textoCompleto = titulo ? `${titulo}. ${texto}` : texto;
    
    const prompt = `Eres un asistente de IA para CityFix AI. Analiza este reporte ciudadano.
    
REPORTE: "${textoCompleto}"

Responde SOLO con JSON, sin explicaciones, en este formato exacto:
{"categoria": "valor", "prioridad": "valor", "confianza": 0}

Categorías posibles: bache, fuga_agua, basura, luminaria, otros
Prioridades posibles: alta, media, baja
Confianza: número del 0 al 100`;

    try {
        const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=${GEMINI_API_KEY}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                contents: [{ parts: [{ text: prompt }] }],
                generationConfig: { temperature: 0.1, maxOutputTokens: 150 }
            })
        });
        
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        
        const data = await response.json();
        let resultadoTexto = data.candidates[0].content.parts[0].text;
        resultadoTexto = resultadoTexto.replace(/```json\n?/g, '').replace(/```\n?/g, '').trim();
        const resultado = JSON.parse(resultadoTexto);
        
        return {
            success: true,
            categoria: resultado.categoria,
            prioridad: resultado.prioridad,
            confianza: resultado.confianza || 80,
            metodo: 'gemini'
        };
    } catch(error) {
        console.error('Error Gemini:', error);
        return null;
    }
}

// ============================================
// Función principal de análisis
// ============================================

async function sugerirConGemini() {
    const titulo = document.getElementById('titulo')?.value || '';
    const descripcion = document.getElementById('descripcion')?.value || '';
    
    if (!descripcion || descripcion.length < 15) {
        mostrarToastIA('📝 Escribe al menos 15 caracteres para analizar', 'warning');
        return;
    }
    
    mostrarToastIA('🤖 Analizando el problema...', 'info');
    
    let resultado = null;
    
    // Intentar usar Gemini si hay API key
    if (GEMINI_API_KEY) {
        resultado = await analizarConGemini(descripcion, titulo);
    }
    
    // Si Gemini falla o no hay key, usar IA local
    if (!resultado || !resultado.success) {
        resultado = analizarConIALocal(descripcion, titulo);
        if (resultado && resultado.metodo === 'local') {
            console.log('Usando IA local (modo offline)');
        }
    }
    
    if (resultado && resultado.success) {
        const categoriaNombre = {
            'bache': '🚧 Bache',
            'fuga_agua': '💧 Fuga de agua',
            'basura': '🗑️ Basura',
            'luminaria': '💡 Luminaria',
            'senal': '⚠️ Señalamiento',
            'otros': '📌 Otros'
        }[resultado.categoria] || resultado.categoria;
        
        const prioridadEmoji = resultado.prioridad == 'alta' ? '🔴' : (resultado.prioridad == 'media' ? '🟡' : '🟢');
        const metodoTexto = resultado.metodo === 'gemini' ? 'Gemini AI' : 'IA Local';
        
        mostrarSugerenciaIA(`
            <i class="fas fa-robot"></i> 
            <strong>${metodoTexto} detectó:</strong><br>
            📂 ${categoriaNombre}<br>
            ${prioridadEmoji} Prioridad: ${resultado.prioridad.toUpperCase()}<br>
            <small>Confianza: ${resultado.confianza}%</small>
        `, resultado.categoria, resultado.prioridad);
    } else {
        mostrarToastIA('⚠️ No se pudo analizar, completa manualmente', 'warning');
    }
}

// ============================================
// Mostrar sugerencia IA
// ============================================

function mostrarSugerenciaIA(mensaje, categoriaSugerida, prioridadSugerida) {
    // Eliminar sugerencia anterior
    const existing = document.getElementById('aiSugerencia');
    if(existing) existing.remove();
    
    const div = document.createElement('div');
    div.id = 'aiSugerencia';
    div.className = 'ai-suggestion';
    div.innerHTML = `
        <div class="flex-grow-1">${mensaje}</div>
        <div class="d-flex gap-2">
            <button class="btn-ai-apply" onclick="aplicarSugerenciaIA('${categoriaSugerida}', '${prioridadSugerida}')">
                <i class="fas fa-check"></i> Aplicar
            </button>
            <button class="btn-ai-close" onclick="this.closest('.ai-suggestion').remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Insertar después del header
    const header = document.querySelector('.header-gradient');
    if(header && header.parentNode) {
        const mainContent = document.querySelector('.main-content');
        if(mainContent && mainContent.firstChild) {
            mainContent.insertBefore(div, mainContent.firstChild.nextSibling);
        } else {
            document.querySelector('.px-3')?.before(div);
        }
    } else {
        document.querySelector('.px-3')?.before(div);
    }
}



function aplicarSugerenciaIA(categoria, prioridad) {
    // Aplicar categoría
    const categoriaEl = document.querySelector(`.category-item[data-cat="${categoria}"]`);
    if(categoriaEl) {
        categoriaEl.click();
    } else {
        // Buscar por otro selector
        const altEl = document.querySelector(`.category-item[onclick*="${categoria}"]`);
        if(altEl) altEl.click();
    }
    
    // Aplicar prioridad
    const prioridadEl = document.querySelector(`.priority-item[data-prioridad="${prioridad}"]`);
    if(prioridadEl) {
        prioridadEl.click();
    }
    
    // Cerrar sugerencia
    const suggestion = document.getElementById('aiSugerencia');
    if(suggestion) suggestion.remove();
    
    mostrarToastIA(`✅ Aplicado: ${categoria} / ${prioridad.toUpperCase()}`, 'success');
}


function mostrarToastIA(mensaje, tipo = 'info') {
    const toast = document.createElement('div');
    toast.className = 'toast-message-ia';
    if(tipo === 'error') toast.style.background = '#ef4444';
    else if(tipo === 'success') toast.style.background = '#10b981';
    else if(tipo === 'warning') toast.style.background = '#f59e0b';
    else toast.style.background = '#cbf163';
    toast.innerHTML = mensaje;
    toast.style.cssText = `
        position: fixed;
        bottom: 100px;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        padding: 12px 24px;
        border-radius: 40px;
        font-size: 14px;
        z-index: 9999;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}