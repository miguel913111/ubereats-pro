class DeliveryBatchModel {
  int? id;
  String? status;
  int? totalOrders;
  double? totalDistanceKm;
  double? estimatedDurationMin;
  String? startedAt;
  String? completedAt;
  List<BatchOrderModel>? orders;
  List<RouteSegmentModel>? routeSegments;

  DeliveryBatchModel({
    this.id,
    this.status,
    this.totalOrders,
    this.totalDistanceKm,
    this.estimatedDurationMin,
    this.startedAt,
    this.completedAt,
    this.orders,
    this.routeSegments,
  });

  double? get totalDistance => totalDistanceKm;
  double? get totalEstimatedTime => estimatedDurationMin;
  double? get earnings => orders?.fold(0.0, (sum, o) => (sum ?? 0) + (o.orderAmount ?? 0));

  DeliveryBatchModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    status = json['status'];
    totalOrders = json['total_orders'];
    totalDistanceKm = json['total_distance_km'] != null ? double.parse(json['total_distance_km'].toString()) : 0;
    estimatedDurationMin = json['estimated_duration_min'] != null ? double.parse(json['estimated_duration_min'].toString()) : 0;
    startedAt = json['started_at'];
    completedAt = json['completed_at'];
    if (json['orders'] != null) {
      orders = [];
      json['orders'].forEach((v) {
        orders!.add(BatchOrderModel.fromJson(v));
      });
    }
    if (json['route_segments'] != null) {
      routeSegments = [];
      json['route_segments'].forEach((v) {
        routeSegments!.add(RouteSegmentModel.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['status'] = status;
    data['total_orders'] = totalOrders;
    data['total_distance_km'] = totalDistanceKm;
    data['estimated_duration_min'] = estimatedDurationMin;
    data['started_at'] = startedAt;
    data['completed_at'] = completedAt;
    if (orders != null) {
      data['orders'] = orders!.map((v) => v.toJson()).toList();
    }
    if (routeSegments != null) {
      data['route_segments'] = routeSegments!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class BatchOrderModel {
  int? sequence;
  int? orderId;
  double? distanceFromPrevKm;
  double? estimatedTimeMin;
  String? pickedUpAt;
  String? deliveredAt;
  String? customerName;
  String? customerPhone;
  String? deliveryAddress;
  double? orderAmount;
  String? paymentMethod;
  String? orderStatus;

  BatchOrderModel({
    this.sequence,
    this.orderId,
    this.distanceFromPrevKm,
    this.estimatedTimeMin,
    this.pickedUpAt,
    this.deliveredAt,
    this.customerName,
    this.customerPhone,
    this.deliveryAddress,
    this.orderAmount,
    this.paymentMethod,
    this.orderStatus,
  });

  double? get distanceFromPrev => distanceFromPrevKm;
  String? get actualArrival => deliveredAt;

  SimpleOrder? get order => SimpleOrder(
    id: orderId,
    storeName: customerName,
    deliveryAddress: deliveryAddress,
    customerPhone: customerPhone,
    paymentMethod: paymentMethod,
    paymentStatus: orderStatus,
  );

  BatchOrderModel.fromJson(Map<String, dynamic> json) {
    sequence = json['sequence'];
    orderId = json['order_id'];
    distanceFromPrevKm = json['distance_from_prev_km'] != null ? double.parse(json['distance_from_prev_km'].toString()) : 0;
    estimatedTimeMin = json['estimated_time_min'] != null ? double.parse(json['estimated_time_min'].toString()) : 0;
    pickedUpAt = json['picked_up_at'];
    deliveredAt = json['delivered_at'];
    customerName = json['customer_name'];
    customerPhone = json['customer_phone'];
    deliveryAddress = json['delivery_address'];
    orderAmount = json['order_amount'] != null ? double.parse(json['order_amount'].toString()) : 0;
    paymentMethod = json['payment_method'];
    orderStatus = json['order_status'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['sequence'] = sequence;
    data['order_id'] = orderId;
    data['distance_from_prev_km'] = distanceFromPrevKm;
    data['estimated_time_min'] = estimatedTimeMin;
    data['picked_up_at'] = pickedUpAt;
    data['delivered_at'] = deliveredAt;
    data['customer_name'] = customerName;
    data['customer_phone'] = customerPhone;
    data['delivery_address'] = deliveryAddress;
    data['order_amount'] = orderAmount;
    data['payment_method'] = paymentMethod;
    data['order_status'] = orderStatus;
    return data;
  }
}

class SimpleOrder {
  final int? id;
  final String? storeName;
  final String? deliveryAddress;
  final String? customerPhone;
  final String? paymentMethod;
  final String? paymentStatus;

  SimpleOrder({this.id, this.storeName, this.deliveryAddress, this.customerPhone, this.paymentMethod, this.paymentStatus});
}

class RouteSegmentModel {
  int? sequence;
  double? fromLat;
  double? fromLng;
  double? toLat;
  double? toLng;
  double? distanceKm;
  double? estimatedMinutes;

  RouteSegmentModel({
    this.sequence,
    this.fromLat,
    this.fromLng,
    this.toLat,
    this.toLng,
    this.distanceKm,
    this.estimatedMinutes,
  });

  RouteSegmentModel.fromJson(Map<String, dynamic> json) {
    sequence = json['sequence'];
    fromLat = json['from_lat'] != null ? double.parse(json['from_lat'].toString()) : null;
    fromLng = json['from_lng'] != null ? double.parse(json['from_lng'].toString()) : null;
    toLat = json['to_lat'] != null ? double.parse(json['to_lat'].toString()) : null;
    toLng = json['to_lng'] != null ? double.parse(json['to_lng'].toString()) : null;
    distanceKm = json['distance_km'] != null ? double.parse(json['distance_km'].toString()) : 0;
    estimatedMinutes = json['estimated_minutes'] != null ? double.parse(json['estimated_minutes'].toString()) : 0;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['sequence'] = sequence;
    data['from_lat'] = fromLat;
    data['from_lng'] = fromLng;
    data['to_lat'] = toLat;
    data['to_lng'] = toLng;
    data['distance_km'] = distanceKm;
    data['estimated_minutes'] = estimatedMinutes;
    return data;
  }
}
