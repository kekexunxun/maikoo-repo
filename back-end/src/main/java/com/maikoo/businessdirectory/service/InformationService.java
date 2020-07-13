package com.maikoo.businessdirectory.service;

import com.maikoo.businessdirectory.model.dto.FlagDTO;

public interface InformationService {

    FlagDTO userHasNewMessage();

    FlagDTO adminHasNewMessage();

    FlagDTO hasNewMessage();
}
