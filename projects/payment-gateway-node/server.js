require('dotenv').config();
const express = require('express');
const stripe = require('stripe')(process.env.STRIPE_SECRET_KEY);
const path = require('path');

const app = express();
const port = process.env.PORT || 3000;

// Middleware
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

// Debug raw body
app.use((req, res, next) => {
    console.log('Raw body:', req.body);
    next();
});

// Provide Stripe publishable key to client (safer than embedding in HTML)
app.get('/config', (req, res) => {
    res.json({
        publishableKey: process.env.STRIPE_PUBLISHABLE_KEY
    });
});

// Serve the index page
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// Serve the about page
app.get('/about', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'about.html'));
});

// Serve the solutions page
app.get('/solutions', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'solutions.html'));
});

// Serve the pricing page
app.get('/pricing', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'pricing.html'));
});

// Serve the contact page
app.get('/contact', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'contact.html'));
});

// Helper function to validate currency
const isValidCurrency = (currency) => {
    const supportedCurrencies = ['usd', 'eur', 'gbp', 'kes'];
    return supportedCurrencies.includes(currency.toLowerCase());
};

// Handle payment processing with token
app.post('/charge', async (req, res) => {
    const { stripeToken, amount, currency = 'usd' } = req.body;

    console.log('Payment request:', { stripeToken, amount, currency });

    if (!stripeToken) {
        console.log('Error: Stripe token missing');
        return res.json({ status: 'error', message: 'Payment token is missing.' });
    }
    
    if (!amount || isNaN(amount) || amount <= 0) {
        console.log('Error: Invalid amount');
        return res.json({ status: 'error', message: 'Enter a valid payment amount.' });
    }
    
    if (!isValidCurrency(currency)) {
        console.log('Error: Unsupported currency', currency);
        return res.json({ status: 'error', message: 'Unsupported currency. Please choose a supported currency.' });
    }

    try {
        // JPY doesn't use decimal places, so handle it differently
        const amountInSmallestUnit = currency.toLowerCase() === 'jpy' 
            ? Math.round(amount)
            : Math.round(amount * 100);
            
        const charge = await stripe.charges.create({
            amount: amountInSmallestUnit,
            currency: currency.toLowerCase(),
            source: stripeToken,
            description: `Payment via SecurePay (${currency.toUpperCase()})`
        });

        console.log('Charge successful:', charge.id);
        res.json({
            status: 'success',
            message: 'Payment processed successfully! Thank you.',
            transaction_id: charge.id,
            currency: currency.toUpperCase()
        });
    } catch (error) {
        console.log('Stripe error:', error.message);
        res.json({
            status: 'error',
            message: error.message || 'Payment processing failed.'
        });
    }
});

// Error handler for missing routes
app.use((req, res) => {
    res.status(404).send('Page not found');
});

// Error handler
app.use((err, req, res, next) => {
    console.error('Server error:', err);
    res.status(500).send('Internal server error');
});

app.listen(port, () => {
    console.log(`Server running on http://localhost:${port}`);
});