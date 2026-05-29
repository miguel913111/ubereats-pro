class StoryModel {
  int? id;
  int? storeId;
  String? title;
  String? image;
  String? video;
  String? type;
  int? duration;
  String? expiresAt;
  int? status;
  String? createdAt;
  String? updatedAt;

  StoryModel({
    this.id,
    this.storeId,
    this.title,
    this.image,
    this.video,
    this.type,
    this.duration,
    this.expiresAt,
    this.status,
    this.createdAt,
    this.updatedAt,
  });

  StoryModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    storeId = json['store_id'];
    title = json['title'];
    image = json['image'];
    video = json['video'];
    type = json['type'];
    duration = json['duration'];
    expiresAt = json['expires_at'];
    status = json['status'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['store_id'] = storeId;
    data['title'] = title;
    data['image'] = image;
    data['video'] = video;
    data['type'] = type;
    data['duration'] = duration;
    data['expires_at'] = expiresAt;
    data['status'] = status;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    return data;
  }
}
