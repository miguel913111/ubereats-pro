class GiftCardUsageModel {
  int? id;
  int? giftCardId;
  int? userId;
  int? orderId;
  double? amount;
  String? createdAt;
  GiftCardUsageModel({
    this.id,
    this.giftCardId,
    this.userId,
    this.orderId,
    this.amount,
    this.createdAt,
  });

  GiftCardUsageModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    giftCardId = json['gift_card_id'];
    userId = json['user_id'];
    orderId = json['order_id'];
    amount = json['amount']?.toDouble();
    createdAt = json['created_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['gift_card_id'] = giftCardId;
    data['user_id'] = userId;
    data['order_id'] = orderId;
    data['amount'] = amount;
    data['created_at'] = createdAt;
    return data;
  }
}
