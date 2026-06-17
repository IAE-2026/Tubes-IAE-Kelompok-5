const state = {
  token: '',
};

const statusNode = document.querySelector('#status');
const resultNode = document.querySelector('#result');
const meOutput = document.querySelector('#meOutput');
const permitsNode = document.querySelector('#permits');
const loginForm = document.querySelector('#loginForm');
const loadPermitsButton = document.querySelector('#loadPermits');

function setStatus(message, type = '') {
  statusNode.textContent = message;
  statusNode.className = `status ${type}`.trim();
}

function showResult(value) {
  resultNode.textContent = JSON.stringify(value, null, 2);
}

async function api(path, options = {}) {
  const headers = {
    'content-type': 'application/json',
    ...(options.headers || {}),
  };
  if (state.token) {
    headers.authorization = `Bearer ${state.token}`;
  }

  const response = await fetch(path, {
    ...options,
    headers,
  });
  const data = await response.json();
  if (!response.ok) {
    const error = new Error(data.error || 'Request failed');
    error.details = data;
    throw error;
  }
  return data;
}

function renderPermits(permits) {
  if (!permits.length) {
    permitsNode.innerHTML = '<p>No permit invoices found.</p>';
    return;
  }

  permitsNode.innerHTML = permits.map((permit) => `
    <article class="permit">
      <div>
        <strong>${permit.permitNumber}</strong>
        <span>${permit.permitType}</span><br>
        <span>Rp ${permit.amount.toLocaleString('id-ID')} · ${permit.payment.status}</span>
      </div>
      <button data-pay="${permit.id}" ${permit.payment.status === 'PAID' ? 'disabled' : ''}>Pay</button>
    </article>
  `).join('');
}

loginForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  setStatus('Logging in');
  try {
    const data = await api('/api/auth/login', {
      method: 'POST',
      body: JSON.stringify({
        email: document.querySelector('#email').value,
        password: document.querySelector('#password').value,
      }),
    });
    state.token = data.token;
    meOutput.textContent = JSON.stringify({
      profile: data.profile,
      local_role: data.local_role,
    }, null, 2);
    showResult(data);
    setStatus('Authenticated', 'ok');
  } catch (error) {
    showResult(error.details || { error: error.message });
    setStatus('Login failed', 'error');
  }
});

loadPermitsButton.addEventListener('click', async () => {
  setStatus('Loading');
  try {
    const data = await api('/api/permits');
    renderPermits(data.data);
    showResult(data);
    setStatus('Loaded', 'ok');
  } catch (error) {
    showResult(error.details || { error: error.message });
    setStatus('Load failed', 'error');
  }
});

permitsNode.addEventListener('click', async (event) => {
  const button = event.target.closest('[data-pay]');
  if (!button) return;

  setStatus('Paying');
  button.disabled = true;
  try {
    const data = await api(`/api/permits/${button.dataset.pay}/pay`, {
      method: 'POST',
      body: JSON.stringify({ method: 'VIRTUAL_ACCOUNT' }),
    });
    showResult(data);
    setStatus('Paid', 'ok');
    loadPermitsButton.click();
  } catch (error) {
    button.disabled = false;
    showResult(error.details || { error: error.message });
    setStatus('Payment failed', 'error');
  }
});
