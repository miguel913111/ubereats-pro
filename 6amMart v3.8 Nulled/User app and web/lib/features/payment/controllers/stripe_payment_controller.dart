import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:nexofood_user/util/app_constants.dart';

class StripePaymentController extends GetxController implements GetxService {
  bool _isLoading = false;
  bool get isLoading => _isLoading;

  String? _clientSecret;
  String? get clientSecret => _clientSecret;

  String? _setupIntentClientSecret;
  String? get setupIntentClientSecret => _setupIntentClientSecret;

  List<dynamic> _savedCards = [];
  List<dynamic> get savedCards => _savedCards;

  String? _customerId;
  String? get customerId => _customerId;

  String? _errorMessage;
  String? get errorMessage => _errorMessage;

  // ── Helpers ─────────────────────────────────────────────────────────

  Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(AppConstants.token);
  }

  Map<String, String> _headers(String? token) => {
    'Content-Type': 'application/json; charset=UTF-8',
    'Authorization': 'Bearer $token',
  };

  // ── API Calls ───────────────────────────────────────────────────────

  /// Criar SetupIntent para salvar cartão
  Future<bool> createSetupIntent() async {
    _setLoading(true);
    _errorMessage = null;
    try {
      final token = await _getToken();
      final response = await http.post(
        Uri.parse('${AppConstants.baseUrl}/api/v1/stripe-connect/setup-intent'),
        headers: _headers(token),
      ).timeout(const Duration(seconds: 30));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['client_secret'] != null) {
        _setupIntentClientSecret = data['client_secret'];
        _setLoading(false);
        return true;
      }
      _errorMessage = data['error'] ?? data['stripe_error'] ?? 'Erro ao criar SetupIntent';
      _setLoading(false);
      return false;
    } catch (e) {
      _errorMessage = 'Erro de rede: $e';
      _setLoading(false);
      return false;
    }
  }

  /// Criar PaymentIntent para cobrança
  Future<Map<String, dynamic>?> createPaymentIntent({
    required int orderId,
    String? paymentMethodId,
  }) async {
    _setLoading(true);
    _errorMessage = null;
    try {
      final token = await _getToken();
      final body = <String, dynamic>{
        'order_id': orderId,
      };
      if (paymentMethodId != null && paymentMethodId.isNotEmpty) {
        body['payment_method'] = paymentMethodId;
      }

      final response = await http.post(
        Uri.parse('${AppConstants.baseUrl}/api/v1/stripe-connect/payment-intent'),
        headers: _headers(token),
        body: jsonEncode(body),
      ).timeout(const Duration(seconds: 30));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['client_secret'] != null) {
        _clientSecret = data['client_secret'];
        _setLoading(false);
        return data;
      }
      _errorMessage = data['error'] ?? 'Erro ao criar PaymentIntent';
      _setLoading(false);
      return null;
    } catch (e) {
      _errorMessage = 'Erro de rede: $e';
      _setLoading(false);
      return null;
    }
  }

  /// Listar cartões salvos
  Future<bool> getSavedCards() async {
    _setLoading(true);
    _errorMessage = null;
    try {
      final token = await _getToken();
      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/api/v1/stripe-connect/payment-methods'),
        headers: _headers(token),
      ).timeout(const Duration(seconds: 30));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200) {
        _savedCards = data['cards'] ?? [];
        _customerId = data['customer_id'];
        _setLoading(false);
        return true;
      }
      _errorMessage = data['error'] ?? 'Erro ao listar cartões';
      _setLoading(false);
      return false;
    } catch (e) {
      _errorMessage = 'Erro de rede: $e';
      _setLoading(false);
      return false;
    }
  }

  /// Obter ou criar Stripe Customer
  Future<bool> getOrCreateCustomer() async {
    _setLoading(true);
    _errorMessage = null;
    try {
      final token = await _getToken();
      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/api/v1/stripe-connect/customer'),
        headers: _headers(token),
      ).timeout(const Duration(seconds: 30));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['customer_id'] != null) {
        _customerId = data['customer_id'];
        _setLoading(false);
        return true;
      }
      _errorMessage = data['error'] ?? 'Erro ao obter customer';
      _setLoading(false);
      return false;
    } catch (e) {
      _errorMessage = 'Erro de rede: $e';
      _setLoading(false);
      return false;
    }
  }

  /// Remover cartão salvo
  Future<bool> removeCard(String paymentMethodId) async {
    _setLoading(true);
    _errorMessage = null;
    try {
      final token = await _getToken();
      final response = await http.delete(
        Uri.parse('${AppConstants.baseUrl}/api/v1/stripe-connect/payment-methods/$paymentMethodId'),
        headers: _headers(token),
      ).timeout(const Duration(seconds: 30));

      final data = jsonDecode(response.body);
      if (response.statusCode == 200) {
        _savedCards.removeWhere((c) => c['id'] == paymentMethodId);
        _setLoading(false);
        update();
        return true;
      }
      _errorMessage = data['error'] ?? 'Erro ao remover cartão';
      _setLoading(false);
      return false;
    } catch (e) {
      _errorMessage = 'Erro de rede: $e';
      _setLoading(false);
      return false;
    }
  }

  // ── Internal ────────────────────────────────────────────────────────

  void _setLoading(bool value) {
    _isLoading = value;
    update();
  }

  void clearError() {
    _errorMessage = null;
    update();
  }

  void setError(String message) {
    _errorMessage = message;
    update();
  }
}
