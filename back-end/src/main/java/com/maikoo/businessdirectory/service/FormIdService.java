package com.maikoo.businessdirectory.service;

import com.maikoo.businessdirectory.model.query.FormIdQuery;

import java.util.List;

public interface FormIdService {
    void insert(List<FormIdQuery> formIdQueryList);
}
