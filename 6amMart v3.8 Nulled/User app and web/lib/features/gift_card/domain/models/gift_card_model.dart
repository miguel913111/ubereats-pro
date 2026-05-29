class GiftCardModel {
  int? id;
  String? title;
  String? description;
  String? code;
  double? amount;
  double? minPurchase;
  double? maxDiscount;
  String? startDate;
  String? expireDate;
  int? totalUses;
  int? usedCount;
  int? limit;
  int? status;
  String? image;
  String? createdBy;
  String? createdAt;
  String? updatedAt;

  GiftCardModel({
    this.id,
    this.title,
    this.description,
    this.code,
    this.amount,
    this.minPurchase,
    this.maxDiscount,
    this.startDate,
    this.expireDate,
    this.totalUses,
    this.usedCount,
    this.limit,
    this.status,
    this.image,
    this.createdBy,
    this.createdAt,
    this.updatedAt,
  });

  GiftCardModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    title = json['title'];
    description = json['description'];
    code = json['code'];
    amount = json['amount']?.toDouble();
    minPurchase = json['min_purchase']?.toDouble();
    maxDiscount = json['max_discount']?.toDouble();
    startDate = json['start_date'];
    expireDate = json['expire_date'];
    totalUses = json['total_uses'];
    usedCount = json['used_count'];
    limit = json['limit'];
    status = json['status'];
    image = json['image'];
    createdBy = json['created_by'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['title'] = title;
    data['description'] = description;
    data['code'] = code;
    data['amount'] = amount;
    data['min_purchase'] = minPurchase;
    data['max_discount'] = maxDiscount;
    data['start_date'] = startDate;
    data['expire_date'] = expireDate;
    data['total_uses'] = totalUses;
    data['used_count'] = usedCount;
    data['limit'] = limit;
    data['status'] = status;
    data['image'] = image;
    data['created_by'] = createdBy;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    return data;
  }
}
