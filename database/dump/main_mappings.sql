INSERT INTO mappings (id, name, definition, created_at, updated_at) VALUES (1, 'habitats', '{
  "_source": {
    "enabled": true
  },
  "properties": {
    "id": {
      "type": "integer"
    },
    "name": {
      "type": "text"
    },
    "mapX": {
      "type": "integer"
    },
    "mapY": {
      "type": "integer"
    },
    "points": {
      "type": "integer"
    },
    "creationDate": {
      "type": "integer"
    },
    "playerID": {
      "type": "integer"
    },
    "publicType": {
      "type": "integer"
    },
    "location": {
      "type": "geo_point"
    }
  }
}', '2017-09-08 17:26:16', '2017-09-13 11:49:30');
INSERT INTO mappings (id, name, definition, created_at, updated_at) VALUES (2, 'alliances', '{
  "_source": {
    "enabled": true
  },
  "properties": {
    "id": {
      "type": "integer"
    },
    "name": {
      "type": "text"
    },
    "playerIDs": {
      "type": "text"
    },
    "rankAverage": {
      "type": "integer"
    },
    "rank": {
      "type": "integer"
    },
    "points": {
      "type": "integer"
    },
    "pointsAverage": {
      "type": "integer"
    },
    "description": {
      "type": "text"
    }
  }
}', '2017-09-08 17:29:26', '2017-09-11 17:18:38');
INSERT INTO mappings (id, name, definition, created_at, updated_at) VALUES (3, 'players', '{
  "_source": {
    "enabled": true
  },
  "properties": {
    "id": {
      "type": "integer"
    },
    "nick": {
      "type": "text"
    },
    "points": {
      "type": "integer"
    },
    "rank": {
      "type": "integer"
    },
    "underAttackProtection": {
      "type": "boolean"
    },
    "onVacation": {
      "type": "boolean"
    }
  }
}', '2017-09-08 17:30:05', '2017-09-11 17:19:02');