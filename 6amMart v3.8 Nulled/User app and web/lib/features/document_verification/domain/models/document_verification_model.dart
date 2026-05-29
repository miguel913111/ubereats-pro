class DocumentVerificationModel {
  int? id;
  String? verifiableType;
  int? verifiableId;
  String? documentType;
  String? documentNumber;
  List<String>? documentImages;
  String? notes;
  String? status;
  String? rejectionReason;
  int? verifiedBy;
  String? verifiedAt;
  String? createdAt;
  String? updatedAt;

  DocumentVerificationModel({
    this.id,
    this.verifiableType,
    this.verifiableId,
    this.documentType,
    this.documentNumber,
    this.documentImages,
    this.notes,
    this.status,
    this.rejectionReason,
    this.verifiedBy,
    this.verifiedAt,
    this.createdAt,
    this.updatedAt,
  });

  DocumentVerificationModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    verifiableType = json['verifiable_type'];
    verifiableId = json['verifiable_id'];
    documentType = json['document_type'];
    documentNumber = json['document_number'];
    if (json['document_images'] != null) {
      documentImages = List<String>.from(json['document_images']);
    }
    notes = json['notes'];
    status = json['status'];
    rejectionReason = json['rejection_reason'];
    verifiedBy = json['verified_by'];
    verifiedAt = json['verified_at'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['verifiable_type'] = verifiableType;
    data['verifiable_id'] = verifiableId;
    data['document_type'] = documentType;
    data['document_number'] = documentNumber;
    data['document_images'] = documentImages;
    data['notes'] = notes;
    data['status'] = status;
    data['rejection_reason'] = rejectionReason;
    data['verified_by'] = verifiedBy;
    data['verified_at'] = verifiedAt;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    return data;
  }
}
