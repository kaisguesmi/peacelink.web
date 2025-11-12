'use strict';

window.addEventListener('DOMContentLoaded', () => {
  // Vérification rapide
  const gSystem = document.getElementById('layer-system');
  const gNodes  = document.getElementById('layer-nodes');
  const gConn   = document.getElementById('layer-connections');
  if (!gSystem || !gNodes || !gConn) {
    console.error('SVG layers introuvables. Vérifie l’ID des <g> dans index.html.');
    return;
  }

  const initialSystemBoundary = { id:'system-1', name:'System', x:280, y:20, width:720, height:700, type:'system' };

  const initialActors = [
    { id:'actor-1', name:'Utilisateur', x:80, y:120, type:'actor' },
    { id:'actor-2', name:'Admin',       x:80, y:420, type:'actor' },
  ];

  const initialUseCases = [
    { id:'uc-1',  name:'Créer initiative',                     x:350, y:60,  type:'usecase' },
    { id:'uc-2',  name:'Définir la date\net la catégorie',     x:680, y:60,  type:'usecase' },
    { id:'uc-3',  name:'Rejoindre\ninitiative',                x:350, y:160, type:'usecase' },
    { id:'uc-4',  name:"S'inscrire à\nune initiative",         x:680, y:160, type:'usecase' },
    { id:'uc-5',  name:'Quitter\ninitiative',                   x:350, y:260, type:'usecase' },
    { id:'uc-6',  name:'Commenter\ninitiative',                 x:350, y:360, type:'usecase' },
    { id:'uc-7',  name:'Réagir à une\ninitiative',             x:680, y:360, type:'usecase' },
    { id:'uc-8',  name:'Approuver\ninitiative',                 x:350, y:460, type:'usecase' },
    { id:'uc-9',  name:'Vérifier la\nconformité',              x:680, y:460, type:'usecase' },
    { id:'uc-10', name:'Supprimer\ninitiative',                 x:350, y:560, type:'usecase' },
    { id:'uc-11', name:'Aider à un\nsignalement',              x:680, y:560, type:'usecase' },
    { id:'uc-12', name:'Gérer les\nparticipations',            x:350, y:630, type:'usecase' },
    { id:'uc-13', name:'Afficher les\nSTATS',                  x:520, y:630, type:'usecase' },
  ];

  const initialConnections = [
    { id:'conn-1', from:'actor-1', to:'uc-1',  type:'association' },
    { id:'conn-2', from:'actor-1', to:'uc-3',  type:'association' },
    { id:'conn-3', from:'actor-1', to:'uc-5',  type:'association' },
    { id:'conn-4', from:'actor-1', to:'uc-6',  type:'association' },
    { id:'conn-5', from:'actor-2', to:'uc-8',  type:'association' },
    { id:'conn-6', from:'actor-2', to:'uc-10', type:'association' },
    { id:'conn-7', from:'actor-2', to:'uc-12', type:'association' },
    { id:'conn-8', from:'actor-2', to:'uc-13', type:'association' },
    { id:'conn-9',  from:'uc-1',  to:'uc-2',  type:'include', label:'include' },
    { id:'conn-10', from:'uc-3',  to:'uc-4',  type:'include', label:'include' },
    { id:'conn-11', from:'uc-8',  to:'uc-9',  type:'include', label:'include' },
    { id:'conn-12', from:'uc-6',  to:'uc-7',  type:'extend',  label:'extend' },
    { id:'conn-13', from:'uc-10', to:'uc-11', type:'extend',  label:'extend' },
    { id:'conn-14', from:'actor-2', to:'actor-1', type:'inheritance' },
  ];

  const ACTOR_W = 100, ACTOR_H = 160;
  const UC_W = 170, UC_H = 80;
  const UC_RX = UC_W / 2, UC_RY = UC_H / 2;

  const byId = {};
  [initialSystemBoundary, ...initialActors, ...initialUseCases].forEach(e => byId[e.id] = e);

  const svgNS = 'http://www.w3.org/2000/svg';
  const svgEl = (name, attrs = {}) => {
    const el = document.createElementNS(svgNS, name);
    Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v));
    return el;
  };

  const getCenter = (el) => {
    if (el.type === 'actor')   return { x: el.x + 50, y: el.y + 80 };
    if (el.type === 'usecase') return { x: el.x + 85, y: el.y + 40 };
    return { x: el.x + el.width / 2, y: el.y + el.height / 2 };
  };
  const midpoint = (a, b, dy = 0) => ({ x: (a.x + b.x)/2, y: (a.y + b.y)/2 + dy });

  // Système
  (function drawSystemBoundary(sb) {
    const rect = svgEl('rect', { x: sb.x, y: sb.y, width: sb.width, height: sb.height, rx: 10, ry: 10, class: 'system-boundary' });
    const title = svgEl('text', { x: sb.x + sb.width - 55, y: sb.y + 18, class: 'system-title' });
    title.textContent = sb.name;
    gSystem.append(rect, title);
  })(initialSystemBoundary);

  // Acteurs
  function drawActor(actor, blue = false) {
    const group = svgEl('g', { transform: `translate(${actor.x},${actor.y})` });
    if (blue) group.setAttribute('class', 'actor-blue');
    const head = svgEl('circle', { cx: 50, cy: 20, r: 12, class: 'actor-stroke' });
    const body = svgEl('line', { x1: 50, y1: 32, x2: 50, y2: 110, class: 'actor-stroke' });
    const arms = svgEl('line', { x1: 10, y1: 60, x2: 90, y2: 60, class: 'actor-stroke' });
    const legL = svgEl('line', { x1: 50, y1: 110, x2: 25, y2: 150, class: 'actor-stroke' });
    const legR = svgEl('line', { x1: 50, y1: 110, x2: 75, y2: 150, class: 'actor-stroke' });
    const label = svgEl('text', { x: 50, y: ACTOR_H - 2, class: 'actor-name' });
    label.textContent = actor.name;
    group.append(head, body, arms, legL, legR, label);
    gNodes.appendChild(group);
  }

  // Use cases
  function drawUseCase(uc) {
    const cx = uc.x + UC_RX;
    const cy = uc.y + UC_RY;
    const ellipse = svgEl('ellipse', { cx, cy, rx: UC_RX, ry: UC_RY, class: 'usecase-ellipse' });
    const text = svgEl('text', { x: cx, y: cy - 10, class: 'usecase-text' });
    uc.name.split('\n').forEach((line, i, arr) => {
      const tspan = svgEl('tspan', { x: cx, dy: i === 0 ? (arr.length > 1 ? 0 : 10) : 18 });
      tspan.textContent = line;
      text.appendChild(tspan);
    });
    gNodes.append(ellipse, text);
  }

  // Connexions
  function drawConnection(c) {
    const from = byId[c.from], to = byId[c.to];
    if (!from || !to) return;
    const A = getCenter(from), B = getCenter(to);

    if (c.type === 'association') {
      gConn.appendChild(svgEl('line', { x1: A.x, y1: A.y, x2: B.x, y2: B.y, class: 'assoc' }));
    } else if (c.type === 'include') {
      gConn.appendChild(svgEl('line', { x1: A.x, y1: A.y, x2: B.x, y2: B.y, class: 'include', 'marker-end': 'url(#arrow-hollow)' }));
      const m = midpoint(A, B, -10);
      const lbl = svgEl('text', { x: m.x, y: m.y, class: 'label' });
      lbl.textContent = '«include»';
      gConn.appendChild(lbl);
    } else if (c.type === 'extend') {
      gConn.appendChild(svgEl('line', { x1: A.x, y1: A.y, x2: B.x, y2: B.y, class: 'extend', 'marker-end': 'url(#arrow-hollow)' }));
      const m = midpoint(A, B, -10);
      const lbl = svgEl('text', { x: m.x, y: m.y, class: 'label' });
      lbl.textContent = '«extend»';
      gConn.appendChild(lbl);
    } else if (c.type === 'inheritance') {
      gConn.appendChild(svgEl('line', { x1: A.x, y1: A.y, x2: B.x, y2: B.y, class: 'inherit', 'marker-end': 'url(#arrow-hollow)' }));
    }
  }

  // Rendu
  initialActors.forEach(a => drawActor(a, a.id === 'actor-2')); // Admin en bleu
  initialUseCases.forEach(drawUseCase);
  initialConnections.forEach(drawConnection);

  console.log('Diagramme rendu ✅');
});
