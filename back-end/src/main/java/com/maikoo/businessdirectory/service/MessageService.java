package com.maikoo.businessdirectory.service;

import com.maikoo.businessdirectory.model.dto.MessageDTO;

import java.util.List;

public interface MessageService {

    List<MessageDTO> listForUser(int pageNumber);


}
