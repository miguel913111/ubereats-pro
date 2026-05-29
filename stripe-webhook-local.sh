#!/bin/bash

echo "=========================================="
echo " STRIPE CONNECT - Webhook Local"
echo "=========================================="
echo ""
echo "Este script inicia o Stripe CLI para encaminhar"
echo "webhooks para o teu projeto local."
echo ""
echo "PASSOS:"
echo "1. Inicia o servidor Laravel (php artisan serve)"
echo "2. Inicia o Stripe CLI listener"
echo ""
echo "=========================================="
read -p "Pressiona ENTER para continuar..."

echo ""
echo "[1/2] A iniciar servidor Laravel..."
php artisan serve --host=0.0.0.0 --port=8000 &
LARAVEL_PID=$!

sleep 3

echo ""
echo "[2/2] A iniciar Stripe CLI webhook listener..."
echo "URL: http://localhost:8000/stripe-connect/webhook"
echo ""
stripe listen --forward-to http://localhost:8000/stripe-connect/webhook

# Quando parar o stripe listen, mata também o servidor Laravel
echo ""
echo "A parar servidor Laravel..."
kill $LARAVEL_PID 2>/dev/null
