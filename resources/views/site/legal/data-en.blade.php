<p>This Data Policy explains, in technical detail, how Unity stores, secures, and retains the personal data described in our <a href="{{ route('legal.privacy') }}">Privacy Policy</a>.</p>

<h2>1. What we store</h2>
<ul>
    <li>Profile data — name, phone number, email, activation status.</li>
    <li>Payment records — amount, gateway, transaction reference, and status (not your card/UPI details, which Razorpay handles directly).</li>
    <li>Device tokens — used solely to deliver push notifications, removable at any time by uninstalling the app or revoking notification permissions.</li>
    <li>In-app notifications — records of messages sent to you (activation status, payment receipts, admin broadcasts).</li>
</ul>

<h2>2. Where your data is stored</h2>
<p>Unity's application and database are hosted in India via Hostinger. Some third-party processors — notably Firebase/Google (authentication and push notifications) — may process data on infrastructure located outside India as part of their global service delivery.</p>

<h2>3. Retention periods</h2>
<p>We retain account data for as long as your account is active. If you request account deletion, we remove or anonymise your personal data within a reasonable period, except where we are required to retain payment records for accounting, tax, or fraud-prevention purposes under applicable law.</p>

<h2>4. Security measures</h2>
<ul>
    <li>Passwords are one-way hashed and never stored or logged in plain text.</li>
    <li>All traffic between your device and Unity is encrypted in transit (HTTPS).</li>
    <li>Payment webhook callbacks are cryptographically signature-verified before any payment is marked successful.</li>
    <li>Admin panel access is role-restricted and limited to approved team members.</li>
</ul>

<h2>5. Third-party data processors</h2>
<table>
    <thead>
        <tr><th>Provider</th><th>Data shared</th><th>Purpose</th></tr>
    </thead>
    <tbody>
        <tr><td>Razorpay</td><td>Payment amount, order reference</td><td>Processing the activation fee</td></tr>
        <tr><td>Firebase / Google</td><td>Phone number, device token</td><td>OTP verification, push notifications</td></tr>
        <tr><td>MSG91</td><td>Phone number</td><td>Transactional SMS delivery</td></tr>
        <tr><td>Hostinger</td><td>All application data</td><td>Hosting &amp; database storage</td></tr>
    </tbody>
</table>

<h2>6. Requesting deletion</h2>
<p>You may request deletion of your account and personal data at any time by contacting <a href="mailto:privacy@unityapp.in">privacy@unityapp.in</a>. We will confirm your identity before processing the request.</p>

<h2>7. Data breach notification</h2>
<p>In the event of a data breach affecting your personal information, we will notify affected users and relevant authorities as required under applicable Indian law.</p>

<h2>8. Contact</h2>
<p>For any data-handling question, contact <a href="mailto:privacy@unityapp.in">privacy@unityapp.in</a>.</p>

<p class="text-sm text-neutral-400">This page is a general template and does not constitute legal advice. It should be reviewed by a qualified lawyer before being relied upon as your organisation's binding data policy.</p>
