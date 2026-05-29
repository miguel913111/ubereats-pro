class StoreTableModel {
  int? id;
  int? storeId;
  String? tableNumber;
  int? capacity;
  String? status;
  String? description;
  String? createdAt;
  String? updatedAt;

  StoreTableModel({
    this.id,
    this.storeId,
    this.tableNumber,
    this.capacity,
    this.status,
    this.description,
    this.createdAt,
    this.updatedAt,
  });

  StoreTableModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    storeId = json['store_id'];
    tableNumber = json['table_number'];
    capacity = json['capacity'];
    status = json['status'];
    description = json['description'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['store_id'] = storeId;
    data['table_number'] = tableNumber;
    data['capacity'] = capacity;
    data['status'] = status;
    data['description'] = description;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    return data;
  }
}
