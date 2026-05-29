class TableReservationModel {
  int? id;
  int? storeId;
  int? storeTableId;
  int? userId;
  String? reservationDate;
  String? reservationTime;
  int? numberOfGuests;
  String? status;
  String? specialRequest;
  String? cancellationReason;
  String? confirmedAt;
  String? createdAt;
  String? updatedAt;

  TableReservationModel({
    this.id,
    this.storeId,
    this.storeTableId,
    this.userId,
    this.reservationDate,
    this.reservationTime,
    this.numberOfGuests,
    this.status,
    this.specialRequest,
    this.cancellationReason,
    this.confirmedAt,
    this.createdAt,
    this.updatedAt,
  });

  TableReservationModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    storeId = json['store_id'];
    storeTableId = json['store_table_id'];
    userId = json['user_id'];
    reservationDate = json['reservation_date'];
    reservationTime = json['reservation_time'];
    numberOfGuests = json['number_of_guests'];
    status = json['status'];
    specialRequest = json['special_request'];
    cancellationReason = json['cancellation_reason'];
    confirmedAt = json['confirmed_at'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['store_id'] = storeId;
    data['store_table_id'] = storeTableId;
    data['user_id'] = userId;
    data['reservation_date'] = reservationDate;
    data['reservation_time'] = reservationTime;
    data['number_of_guests'] = numberOfGuests;
    data['status'] = status;
    data['special_request'] = specialRequest;
    data['cancellation_reason'] = cancellationReason;
    data['confirmed_at'] = confirmedAt;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    return data;
  }
}
